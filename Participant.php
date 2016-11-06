<?php

namespace SecretSanta;

/**
 * Class Participant
 * @package SecretSanta
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
	public function getEmail() {
		return $this->sEmail;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->sIdentifier;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->sName;
	}

	/**
	 * @return Participant
	 */
	public function getPartner() {
		return $this->oPartner;
	}

	/**
	 * @return Participant
	 */
	public function getRecipient() {
		return $this->oRecipient;
	}

	/**
	 * @return bool
	 */
	public function hasPartner() {
		return ( $this->oPartner instanceof Participant );
	}

	/**
	 * @return bool
	 */
	public function hasRecipient() {
		return ( $this->oRecipient instanceof Participant );
	}

	/**
	 * @param string $sEmail
	 * @return $this
	 */
	public function setEmail( $sEmail ) {
		$this->sEmail = $sEmail;
		return $this;
	}

	/**
	 * @param string $sIdentifier
	 * @return $this
	 */
	public function setIdentifier( $sIdentifier ) {
		$this->sIdentifier = $sIdentifier;
		return $this;
	}

	/**
	 * @param string $sName
	 * @return $this
	 */
	public function setName( $sName ) {
		$this->sName = $sName;
		return $this;
	}

	/**
	 * @param Participant $oPartner
	 * @return $this
	 */
	public function setPartner( Participant $oPartner ) {
		$this->oPartner = $oPartner;
		return $this;
	}

	/**
	 * @param Participant $oRecipient
	 * @return $this
	 */
	public function allocate( Participant $oRecipient ) {
		$this->oRecipient = $oRecipient;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function deallocate() {
		$this->oRecipient = null;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function divorce() {
		$this->oPartner = null;
		return $this;
	}
}