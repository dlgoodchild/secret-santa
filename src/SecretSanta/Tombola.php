<?php declare( strict_types=1 );

namespace DLGoodchild\SecretSanta;

/**
 * Class Tombola
 * @package DLGoodchild\SecretSanta
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
	public function getParticipantByEmail( string $sEmail ): Participant {
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
	public function getParticipantByIdentifier( string $sIdentifier ): Participant {
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
	public function getParticipantByName( string $sName ): Participant {
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
	public function getParticipants(): array {
		return $this->aParticipants;
	}

	/**
	 * @param string $sIdentifierA
	 * @param string $sIdentifierB
	 * @return Tombola
	 * @throws \Exception
	 */
	public function addCoupleByIdentifier( string $sIdentifierA, string $sIdentifierB ): Tombola {
		$oParticipantA = $this->getParticipantByIdentifier( $sIdentifierA );
		$oParticipantB = $this->getParticipantByIdentifier( $sIdentifierB );

		$oParticipantA->setPartner( $oParticipantB );
		$oParticipantB->setPartner( $oParticipantA );
		return $this;
	}

	/**
	 * @param Participant $oPerson
	 * @return Tombola
	 */
	public function addParticipant( Participant $oPerson ): Tombola {
		$this->aParticipants[] = $oPerson;
		return $this;
	}

	/**
	 * @param Participant[] $aParticipants
	 * @return Tombola
	 */
	public function addParticipants( array $aParticipants ): Tombola {
		$this->aParticipants = array_merge( $this->aParticipants, $aParticipants );
		return $this;
	}

	/**
	 * @param Participant $oParticipant
	 * @param Participant[] $aRecipients
	 * @return Tombola
	 */
	private function allocate( Participant $oParticipant, array $aRecipients ): Tombola {
		$nRandIndex = rand( 0, count( $aRecipients )-1 );
		$oParticipant->allocate( $aRecipients[$nRandIndex] );
		return $this;
	}

	/**
	 * @return Tombola
	 * @throws \Exception
	 */
	public function generate(): Tombola {
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
	private function obtainAvailableRecipientsForParticipant( Participant $oParticipant ): array {
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
	private function obtainParticipantsWithRecipient(): array {
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
	private function obtainParticipantsWithoutRecipient(): array {
		$aExtract = array();
		foreach ( $this->aParticipants as $nIndex => $oParticipant ) {
			if ( !$oParticipant->hasRecipient() ) {
				$aExtract[] = $this->aParticipants[$nIndex];
			}
		}
		return $aExtract;
	}
}