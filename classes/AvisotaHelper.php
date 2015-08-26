<?php

namespace HeimrichHannot\FormHybrid;

use Avisota\Contao\Entity\Recipient;
use Avisota\Contao\Entity\Subscription;
use Avisota\Contao\Message\Core\Renderer\MessageRendererInterface;
use Avisota\Contao\Subscription\SubscriptionManager;
use Avisota\Transport\TransportInterface;
use Contao\Doctrine\ORM\EntityHelper;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use Contao\Doctrine\ORM\Exception\UnknownPropertyException;

class AvisotaHelper {

	const RECIPIENT_MODE_USE_MEMBER_DATA = 'member';
	const RECIPIENT_MODE_USE_SUBMISSION_DATA = 'submission';

	public static function addRecipient($strEmail, $arrMailingListIds, $strConfirmationMessageId, $intJumpTo)
	{
		$repository = EntityHelper::getRepository('Avisota\Contao:Recipient');

		if (($recipient  = $repository->findOneBy(array('email' => $strEmail))) === null) {
			$recipient = new Recipient();

			$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];
			try {
				$entityAccessor->setProperty($recipient, 'email', $strEmail);
			}
			catch (UnknownPropertyException $e) {
				// gracefully ignore non-public properties
			}
		}

		$entityManager = EntityHelper::getEntityManager();
		$entityManager->persist($recipient);

		$mailingLists = static::loadMailingLists(deserialize($arrMailingListIds, true));

		$subscriptionManager = $GLOBALS['container']['avisota.subscription'];

		$subscriptions = $subscriptionManager->subscribe(
			$recipient,
			$mailingLists,
			SubscriptionManager::OPT_IGNORE_BLACKLIST | SubscriptionManager::OPT_INCLUDE_EXISTING
		);

		$subscriptions = array_filter(
			$subscriptions,
			function (Subscription $subscription) {
				return !$subscription->getActive();
			}
		);

		/** @var Subscription[] $subscriptions */

		$_SESSION['AVISOTA_LAST_SUBSCRIPTIONS'] = $subscriptions;

		$entityManager->flush();

		if (count($subscriptions)) {
			$eventDispatcher = $GLOBALS['container']['event-dispatcher'];

			if ($intJumpTo) {
				$event = new GetPageDetailsEvent($intJumpTo);
				$eventDispatcher->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

				$pageDetails = $event->getPageDetails();
			}
			else {
				$pageDetails = $GLOBALS['objPage']->row();
			}

			$event = new GenerateFrontendUrlEvent($pageDetails);
			$eventDispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

			$query = array('token' => array());

			foreach ($subscriptions as $subscription) {
				$query['token'][] = $subscription->getActivationToken();
			}

			$base        = \Environment::get('base');
			$url         = $base . $event->getUrl() . '?' . http_build_query($query);

			\System::loadLanguageFile('fe_avisota_subscription');

			$data = array(
				'link'          => array(
					'url'  => $url,
					'text' => $GLOBALS['TL_LANG']['fe_avisota_subscription']['confirm'],
				),
				'subscriptions' => $subscriptions,
			);

			static::sendAvisotaEMail($strConfirmationMessageId, $recipient, $data);
		}
	}

	/**
	 * Load multiple mailing lists by ID.
	 *
	 * @param $mailingListIds
	 *
	 * @return array|MailingList[]
	 */
	protected static function loadMailingLists($mailingListIds)
	{
		$mailingLists          = array();
		$mailingListRepository = EntityHelper::getRepository('Avisota\Contao:MailingList');
		$queryBuilder          = $mailingListRepository->createQueryBuilder('ml');
		$expr                  = $queryBuilder->expr();
		$queryBuilder
			->select('ml')
			->where($expr->in('ml.id', ':ids'))
			->setParameter('ids', $mailingListIds);
		$query = $queryBuilder->getQuery();
		/** @var MailingList[] $result */
		return $query->getResult();
	}

	public static function sendAvisotaEMail($strConfirmationMessageId, $varRecipientOrEmail, $arrData = array(), $strSalutationGroupId = null, $intRecipientMode = null, $arrFiles = array())
	{
		$objMessage = static::getAvisotaMessage($strConfirmationMessageId);

		static::doSendAvisotaEMail($objMessage, $varRecipientOrEmail, $arrData, $strSalutationGroupId, $intRecipientMode, $arrFiles);
	}

	public static function sendAvisotaEMailByMessage($objMessage, $varRecipientOrEmail, $arrData = array(), $strSalutationGroupId = null, $intRecipientMode = null, $arrFiles = array())
	{
		static::doSendAvisotaEMail($objMessage, $varRecipientOrEmail, $arrData, $strSalutationGroupId, $intRecipientMode, $arrFiles);
	}

	private static function doSendAvisotaEMail($objMessage, $varRecipientOrEmail, $arrData = array(), $strSalutationGroupId = null, $intRecipientMode = null, $arrFiles = array())
	{
		if ($objMessage) {
			// possible attachments
			if (!empty($arrFiles)) {
				$objMessage->setAddFile(true);
			}

			foreach ($arrFiles as $strFileUuid) {
				$objMessage->setFiles(array($strFileUuid));
			}

			/** @var MessageRendererInterface $renderer */
			$renderer = $GLOBALS['container']['avisota.message.renderer'];

			$template = $renderer->renderMessage($objMessage);

			if (is_array($varRecipientOrEmail))
			{
				foreach ($varRecipientOrEmail as $recipient)
				{
					static::doTransportAvisotaEmail(
						$template,
						static::prepareRecipient($recipient, $arrData, $strSalutationGroupId, $intRecipientMode),
						$arrData
					);
				}
			}
			else
			{
				static::doTransportAvisotaEmail(
					$template,
					static::prepareRecipient($varRecipientOrEmail, $arrData, $strSalutationGroupId, $intRecipientMode),
					$arrData
				);
			}
		}
	}

	public static function doTransportAvisotaEmail($template, $objRecipient, $arrData)
	{
		if (!$objRecipient)
			return;

		$mail = $template->render(
			$objRecipient,
			$arrData
		);

		/** @var TransportInterface $transport */
		$transport = $GLOBALS['container']['avisota.transport.default'];
		$transport->send($mail);
	}

	public static function prepareRecipient($varRecipientOrEmail, $arrData, $strSalutationGroupId, $intRecipientMode)
	{
		if (!$varRecipientOrEmail instanceof Recipient) {
			$repository = EntityHelper::getRepository(
				'Avisota\Contao:Recipient'
			);

			$entityAccessor = $GLOBALS['container']['doctrine.orm.entityAccessor'];

			if (($objRecipient = $repository->findOneBy(
					array('email' => $varRecipientOrEmail)
				)) === null
			) {
				$objRecipient = new Recipient();

				try {
					$entityAccessor->setProperty(
						$objRecipient, 'email', $varRecipientOrEmail
					);
				} catch (UnknownPropertyException $e) {
					// gracefully ignore non-public properties
				}
			}

			$arrDataToAdd = array();
			switch ($intRecipientMode)
			{
				case static::RECIPIENT_MODE_USE_SUBMISSION_DATA:
					$arrDataToAdd = $arrData;

					// add submission data to recipient
					$synonymizer = $GLOBALS['container']['avisota.recipient.synonymizer'];

					$arrDataSynonymized = array();
					foreach ($arrDataToAdd as $strField => $varValue)
					{
						$arrDataSynonymized[$strField] = $varValue;

						$arrSynonyms    = $synonymizer->findSynonyms($strField);

						if ($arrSynonyms) {
							foreach ($arrSynonyms as $strSynonym) {
								$arrDataSynonymized[$strSynonym] = $varValue;
							}
						}
					}
					$arrDataToAdd = $arrDataSynonymized;
					break;
				case static::RECIPIENT_MODE_USE_MEMBER_DATA:
					$arrDataToAdd = static::addMemberProperties(array('email' => $varRecipientOrEmail));
					break;
			}

			foreach ($arrDataToAdd as $strField => $varValue)
			{
				try {
					if (!is_array($varValue))
					{
						$entityAccessor->setProperty(
							$objRecipient, $strField, $varValue
						);
					}
				} catch (UnknownPropertyException $e) {
					// gracefully ignore non-public properties
				}
			}

			// set salutation
			if ($strSalutationGroupId)
			{
				$selector = $GLOBALS['container']['avisota.salutation.selector'];
				$tagReplacer = $GLOBALS['container']['avisota.message.tagReplacementEngine'];

				$salutationGroupRepository = EntityHelper::getRepository('Avisota\Contao:SalutationGroup');
				$salutationGroup           = $salutationGroupRepository->find($strSalutationGroupId);

				$salutation = $selector->selectSalutation($objRecipient, $salutationGroup);

				if ($salutation)
				{
					$synonymizer = $GLOBALS['container']['avisota.recipient.synonymizer'];

					$pattern = $salutation->getSalutation();
					$details = $synonymizer->expandDetailsWithSynonyms($objRecipient);
					$buffer  = $tagReplacer->parse($pattern, $details);

					$entityAccessor->setProperty(
						$objRecipient, 'salutation', $buffer
					);
				}
			}
		} else {
			$objRecipient = $varRecipientOrEmail;
		}

		return $objRecipient;
	}

	public static function getAvisotaMessage($intMessageId)
	{
		if ($intMessageId)
		{
			$messageRepository = EntityHelper::getRepository(
				'Avisota\Contao:Message'
			);
			return $messageRepository->find($intMessageId);
		}
	}

	public static function addMemberProperties($arrDetails)
	{
		$arrRecipientFields = array();
		\Controller::loadDataContainer('orm_avisota_recipient');

		foreach ($GLOBALS['TL_DCA']['orm_avisota_recipient']['metapalettes']['default'] as $strPalette => $arrFields)
		{
			$arrRecipientFields = array_merge($arrRecipientFields, $arrFields);
		}

		$objMember = \MemberModel::findByEmail($arrDetails['email']);

		foreach ($arrRecipientFields as $strName)
		{
			// ignore member data if a csv column is already there
			if ($arrDetails[$strName])
				continue;

			// ignore salutations inserted in the backend
			if ($strName == 'salutation')
				continue;

			if ($strName != 'email')
				$arrDetails[$strName] = '';

			// enhance with member data if existing
			if ($objMember !== null)
			{
				if ($objMember->$strName)
					$arrDetails[$strName] = $objMember->$strName;
				else
				{
					// try synonyms
					$synonymizer = $GLOBALS['container']['avisota.recipient.synonymizer'];
					$arrSynonyms    = $synonymizer->findSynonyms($strName);

					if ($arrSynonyms) {
						foreach ($arrSynonyms as $strSynonym) {
							if ($objMember->$strSynonym)
							{
								$arrDetails[$strName] = $objMember->$strSynonym;
							}
						}
					}
				}
			}
		}

		return $arrDetails;
	}

}