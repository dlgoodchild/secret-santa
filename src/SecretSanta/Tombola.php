<?php declare( strict_types=1 );

namespace DLGoodchild\SecretSanta;

/**
 * Class Tombola
 * @package DLGoodchild\SecretSanta
 */
class Tombola {

	/**
	 * @var int
	 */
	protected $nAttempts;

	/**
	 * @var Participant[]
	 */
	protected $aParticipants;

	/**
	 * @var int
	 */
	protected $nRecipientsEach;

	/**
	 * @param int $nRecipientsEach
	 * @param int $nAttempts
	 */
	public function __construct( int $nRecipientsEach = 1, $nAttempts = 100 ) {
		$this->nRecipientsEach = $nRecipientsEach;
		$this->nAttempts = $nAttempts;

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
		$nMaxAttempts = $this->nAttempts; // some sane limit.

		while ( $nAttempt < $nMaxAttempts ) {
			try {
				foreach ( $this->aParticipants as $oParticipant ) {
					if ( count( $oParticipant->getRecipients() ) === $this->nRecipientsEach ) {
						continue;
					}

					for ( $i = 0; $i < $this->nRecipientsEach; $i++ ) {
						$aPossibleRecipients = $this->obtainAvailableRecipientsForParticipant( $oParticipant );
						if ( empty( $aPossibleRecipients ) ) {
							throw new \Exception(
								sprintf( 'This attempt resulted in participant %s having no available recipients (i: %s',
									$oParticipant->getIdentifier(), $i
								)
							);
						}
						$this->allocate( $oParticipant, $aPossibleRecipients );
					}
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
		// build a fresh list of remaining recipients
		$aRemainingRecipientIdentifiers = array();

		/* @var Participant $oTestParticipant */
		foreach ( $this->aParticipants as $nIndex => $oTestParticipant ) {
			$aRemainingRecipientIdentifiers[$oTestParticipant->getIdentifier()] = $this->nRecipientsEach;
		}

		// naff, todo, fix.
		foreach ( $this->aParticipants as $nIndex => $oTestParticipant ) {
			if ( $oTestParticipant->hasRecipient() ) {
				foreach ( $oTestParticipant->getRecipients() as $oRecipient ) {
					$aRemainingRecipientIdentifiers[$oRecipient->getIdentifier()]--;
				}
			}
		}

		// remove self
		$aRemainingRecipientIdentifiers[$oParticipant->getIdentifier()] = 0;

		// flat out remove the partner and their recipients from the possible remaining recipients
		if ( $oParticipant->hasPartner() ) {
			$oPartner = $oParticipant->getPartner();
			$aRemainingRecipientIdentifiers[$oPartner->getIdentifier()] = 0;

			$aPartnerRecipientIdentifiers = $oPartner->getRecipients();
			foreach ( $aPartnerRecipientIdentifiers as $oPartnerRecipient ) {
				$aRemainingRecipientIdentifiers[$oPartnerRecipient->getIdentifier()] = 0;
			}
		}

		// convert the remaining identifiers (>0) to participants
		$aRemainingRecipients = array();
		foreach ( $this->aParticipants as $nIndex => $oTestParticipant ) {
			if ( $aRemainingRecipientIdentifiers[$oTestParticipant->getIdentifier()] == 0 ) {
				continue;
			}
			$aRemainingRecipients[] = $this->aParticipants[$nIndex];
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