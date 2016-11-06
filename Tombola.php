<?php

namespace SecretSanta;

/**
 * Class Tombola
 * @package SecretSanta
 */
class Tombola {

	/**
	 * @var Participant[]
	 */
	protected $aParticipants;

	/**
	 *
	 */
	public function __construct() {
		$this->aParticipants = array();
	}

	/**
	 * @param string $sEmail
	 * @return Participant
	 * @throws \Exception
	 */
	public function getParticipantByEmail( $sEmail ) {
		foreach ( $this->aParticipants as $oParticipant ) {
			if ( $oParticipant->getEmail() == $sEmail ) {
				return $oParticipant;
			}
		}
		throw new \Exception( sprintf( 'Failed to find participant with email "%s"', $sEmail ) );
	}

	/**
	 * @param string $sIdentifier
	 * @return Participant
	 * @throws \Exception
	 */
	public function getParticipantByIdentifier( $sIdentifier ) {
		foreach ( $this->aParticipants as $oParticipant ) {
			if ( $oParticipant->getIdentifier() == $sIdentifier ) {
				return $oParticipant;
			}
		}
		throw new \Exception( sprintf( 'Failed to find participant with identifier "%s"', $sIdentifier ) );
	}

	/**
	 * @param string $sName
	 * @return Participant
	 * @throws \Exception
	 */
	public function getParticipantByName( $sName ) {
		foreach ( $this->aParticipants as $oParticipant ) {
			if ( $oParticipant->getName() == $sName ) {
				return $oParticipant;
			}
		}
		throw new \Exception( sprintf( 'Failed to find participant with name "%s"', $sName ) );
	}

	/**
	 * @return Participant[]
	 */
	public function getParticipants() {
		return $this->aParticipants;
	}

	/**
	 * @param string $sIdentifierA
	 * @param string $sIdentifierB
	 * @return $this
	 * @throws \Exception
	 */
	public function addCoupleByIdentifier( $sIdentifierA, $sIdentifierB ) {
		$oParticipantA = $this->getParticipantByIdentifier( $sIdentifierA );
		$oParticipantB = $this->getParticipantByIdentifier( $sIdentifierB );

		$oParticipantA->setPartner( $oParticipantB );
		$oParticipantB->setPartner( $oParticipantA );
		return $this;
	}

	/**
	 * @param Participant $oPerson
	 * @return $this
	 */
	public function addParticipant( Participant $oPerson ) {
		$this->aParticipants[] = $oPerson;
		return $this;
	}

	/**
	 * @param Participant[] $aParticipants
	 * @return $this
	 */
	public function addParticipants( array $aParticipants ) {
		$this->aParticipants = array_merge( $this->aParticipants, $aParticipants );
		return $this;
	}

	/**
	 * @param Participant $oParticipant
	 * @param Participant[] $aRecipients
	 * @return $this
	 */
	private function allocate( Participant $oParticipant, array $aRecipients ) {
		$nRandIndex = rand( 0, count( $aRecipients )-1 );
		$oParticipant->allocate( $aRecipients[$nRandIndex] );
		return $this;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function generate() {
		$nAttempt = 0;
		$nMaxAttempts = 100; // some sane limit.

		while ( $nAttempt < $nMaxAttempts ) {
			try {
				foreach ( $this->aParticipants as $oParticipant ) {
					if ( $oParticipant->hasRecipient() ) {
						continue;
					}

					$aPossibleRecipients = $this->obtainAvailableRecipientsForParticipant( $oParticipant );
					if ( empty( $aPossibleRecipients ) ) {
						throw new \Exception( 'This attempt resulted in a participant having no available recipients' );
					}
					$this->allocate( $oParticipant, $aPossibleRecipients );
				}
				break;
			}
			catch ( \Exception $oE ) {
				foreach ( $this->aParticipants as $oParticipant ) {
					$oParticipant->deallocate();
				}
			}
			$nAttempt++;
		}

		if ( $nAttempt == ($nMaxAttempts - 1) ) {
			throw new \Exception( 'Failed to obtain a match' );
		}
		/*
		else {
			foreach ( $this->aParticipants as $oParticipant ) {
				printf(
					'%s will buy for %s\n<br />',
					$oParticipant->getName(),
					$oParticipant->getRecipient()->getName()
				);
			}
		}*/

		return $this;
	}

	/**
	 * @param Participant $oParticipant
	 * @return Participant[]
	 */
	private function obtainAvailableRecipientsForParticipant( Participant $oParticipant ) {
		$aAllocatedRecipientIdentifiers = array();
		foreach ( $this->aParticipants as $nIndex => $oTestParticipant ) {
			if ( $oTestParticipant->hasRecipient() ) {
				$aAllocatedRecipientIdentifiers[] = $oTestParticipant->getRecipient()->getIdentifier();
			}
		}

		$sPartnerIdentifier = $oParticipant->hasPartner()? $oParticipant->getPartner()->getIdentifier(): false;
		$sSelfIdentifier = $oParticipant->getIdentifier();

		$aRemainingRecipients = array();
		foreach ( $this->aParticipants as $nIndex => $oTestParticipant ) {
			$sTestIdentifier = $oTestParticipant->getIdentifier();
			if ( !in_array( $sTestIdentifier, $aAllocatedRecipientIdentifiers )
				&& $sTestIdentifier != $sPartnerIdentifier
				&& $sTestIdentifier != $sSelfIdentifier ) {
				$aRemainingRecipients[] = $this->aParticipants[$nIndex];
			}
		}
		return $aRemainingRecipients;
	}

	/**
	 * @return Participant[]
	 */
	private function obtainParticipantsWithRecipient() {
		$aExtract = array();
		foreach ( $this->aParticipants as $nIndex => $oParticipant ) {
			if ( $oParticipant->hasRecipient() ) {
				$aExtract[] = $this->aParticipants[$nIndex];
			}
		}
		return $aExtract;
	}

	/**
	 * @return Participant[]
	 */
	private function obtainParticipantsWithoutRecipient() {
		$aExtract = array();
		foreach ( $this->aParticipants as $nIndex => $oParticipant ) {
			if ( !$oParticipant->hasRecipient() ) {
				$aExtract[] = $this->aParticipants[$nIndex];
			}
		}
		return $aExtract;
	}
}