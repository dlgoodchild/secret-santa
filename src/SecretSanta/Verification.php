<?php declare( strict_types = 1 );

namespace DLGoodchild\SecretSanta;

/**
 * Class Verification
 * @package DLGoodchild\SecretSanta
 */
class Verification {

	/**
	 * @param Participant[] $aParticipants
	 * @param int $nRecipientsEach
	 * @return bool
	 */
	public function verify( array $aParticipants, int $nRecipientsEach ): bool {
		return (
			$this->passEachHaveRequiredRecipientCount( $aParticipants, $nRecipientsEach )
			&& $this->passNoParticipantHasSelf( $aParticipants )
			&& $this->passEachParticipantReceivesRequiredCount( $aParticipants, $nRecipientsEach )
		);
	}

	/**
	 * @param Participant[] $aParticipants
	 * @param int $nRecipientsEach
	 * @return bool
	 */
	protected function passEachHaveRequiredRecipientCount( array $aParticipants, int $nRecipientsEach ): bool {
		return !count(
			array_filter( $aParticipants,
				function( Participant $oParticipant ) use ( $nRecipientsEach ) {
					return ( count( $oParticipant->getRecipients() ) != $nRecipientsEach );
				}
			)
		);
	}

	/**
	 * @param array $aParticipants
	 * @param int $nRecipientsEach
	 * @return bool
	 */
	protected function passEachParticipantReceivesRequiredCount( array $aParticipants, int $nRecipientsEach ): bool {
		return !count(
			array_filter( $aParticipants,
				function ( Participant $oParticipant ) use ( $aParticipants, $nRecipientsEach ) {
					$nTally = 0;
					/* @var Participant $oTestParticipant */
					foreach ( $aParticipants as $oTestParticipant ) {
						$nTally += in_array( $oParticipant->getIdentifier(),
							array_map( function( Participant $oParticipant ) { return $oParticipant->getIdentifier(); }, $oTestParticipant->getRecipients() )
						)? 1: 0;
					}
					return ($nTally !== $nRecipientsEach );
				}
			)
		);
	}

	/**
	 * @param Participant[] $aParticipants
	 * @return bool
	 */
	protected function passNoParticipantHasSelf( array $aParticipants ): bool {
		return !count(
			array_filter( $aParticipants,
				function ( Participant $oParticipant ) {
					return in_array( $oParticipant->getIdentifier(),
						array_map( function( Participant $oParticipant ) { return $oParticipant->getIdentifier(); }, $oParticipant->getRecipients() )
					);
				}
			)
		);
	}
}