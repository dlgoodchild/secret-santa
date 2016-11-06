<?php declare( strict_types=1 );

namespace DLGoodchild\SecretSanta;

/**
 * Class Participant
 * @package DLGoodchild\SecretSanta
 */
class Participant {

	/**
	 * @var string
	 */
	protected $sEmail;

	/**
	 * @var string
	 */
	protected $sIdentifier;

	/**
	 * @var string
	 */
	protected $sName;

	/**
	 * @var Participant
	 */
	protected $oPartner;

	/**
	 * @var Participant
	 */
	protected $oRecipient;

	/**
	 * @return string
	 */
	public function getEmail(): string {
		return $this->sEmail;
	}

	/**
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->sIdentifier;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->sName;
	}

	/**
	 * @return Participant
	 */
	public function getPartner(): Participant {
		return $this->oPartner;
	}

	/**
	 * @return Participant
	 */
	public function getRecipient(): Participant {
		return $this->oRecipient;
	}

	/**
	 * @return bool
	 */
	public function hasPartner(): bool {
		return ( $this->oPartner instanceof Participant );
	}

	/**
	 * @return bool
	 */
	public function hasRecipient(): bool {
		return ( $this->oRecipient instanceof Participant );
	}

	/**
	 * @param string $sEmail
	 * @return Participant
	 */
	public function setEmail( string $sEmail ): Participant {
		$this->sEmail = $sEmail;
		return $this;
	}

	/**
	 * @param string $sIdentifier
	 * @return Participant
	 */
	public function setIdentifier( string $sIdentifier ): Participant {
		$this->sIdentifier = $sIdentifier;
		return $this;
	}

	/**
	 * @param string $sName
	 * @return Participant
	 */
	public function setName( string $sName ): Participant {
		$this->sName = $sName;
		return $this;
	}

	/**
	 * @param Participant $oPartner
	 * @return Participant
	 */
	public function setPartner( Participant $oPartner ): Participant {
		$this->oPartner = $oPartner;
		return $this;
	}

	/**
	 * @param Participant $oRecipient
	 * @return Participant
	 */
	public function allocate( Participant $oRecipient ): Participant {
		$this->oRecipient = $oRecipient;
		return $this;
	}

	/**
	 * @return Participant
	 */
	public function deallocate() {
		$this->oRecipient = null;
		return $this;
	}

	/**
	 * @return Participant
	 */
	public function divorce() {
		$this->oPartner = null;
		return $this;
	}
}