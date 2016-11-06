<?php declare( strict_types=1 );

error_reporting( E_ALL );
ini_set( 'display_errors', 'on' );

$oComposer = include_once( __DIR__.'/src/vendor/autoload.php' );

$oMandrill = new Mandrill( '...' );
$aMessageParams = array(
	'html' => '<p>Example HTML content</p>',
	//'text' => 'Example text content',
	'subject' => 'example subject',
	'from_email' => 'secret.santa@d....co.uk',
	'from_name' => 'Secret Santa',
	'to' => array(
		array(
			'email' => 'test@d....co.uk',
			'name' => 'Dave Goodchild',
			'type' => 'to'
		)
	),
	'headers' => array( 'Reply-To' => 'secret.santa@d....co.uk'),
	'important' => false,
	'track_opens' => false,
	'track_clicks' => false,
	'tags' => array( 'secret-santa' )
);

// these would be supplied from a DB or a web form (or API)
$aParticipants = array(
	array( 'N G', 'na', 'abc123@gmail.com' ),
	array( 'J G', 'jo', 'abc123@gmail.com' ),
	array( 'D G', 'da', 'abc123@d....co.uk' ),
	array( 'P G', 'pa', 'abc123@p....net' ),
	array( 'T G', 'ti', 'abc123@gmail.com' ),
	array( 'E G', 'el', 'abc123@hotmail.co.uk' ),
	array( 'X G', 'nn', 'abc123@gmail.com' ),
	array( 'G M', 'ga', 'abc123@btinternet.com' ),
	array( 'A D', 'au', 'abc123@yahoo.es' )
);

$aCouples = array(
	array( 'jo', 'ga' ),
	array( 'nn', 'ti' ),
	array( 'au', 'da' )
);

$oTombola = new \DLGoodchild\SecretSanta\Tombola();

foreach ( $aParticipants as $aParticipant ) {
	$oTombola->addParticipant(
		new \DLGoodchild\SecretSanta\Participant(
			$aParticipant[0],
			$aParticipant[2],
			$aParticipant[1]
		)
	);
}

foreach ( $aCouples as $aCouple ) {
	$oTombola->addCoupleByIdentifier( $aCouple[0], $aCouple[1] );
}

$oTombola->generate();

$aParticipants = $oTombola->getParticipants();
foreach ( $aParticipants as $oParticipant ) {
	$aParams = $aMessageParams;
	$aParams['to'] = array(
		array(
			//'email' => $oParticipant->getEmail(),
			'email' => $oParticipant->getIdentifier().'test.secret@d....co.uk',
			'name' => $oParticipant->getName(),
			'type' => 'to'
		)
	);

	$aParams['subject'] = sprintf(
		'Secret Santa: %s, you have to buy a present for...',
		explode( ' ', $oParticipant->getName() )[0]
	);

	//echo $aParams['subject'].' '.$oParticipant->getRecipient()->getName().'<br />';
	$aParams['html'] = sprintf( "
			<p>Hi %s,</p>
			<p><strong>The Goodchild (plus Martin and Díaz) Secret Santa Tombola has selected...</strong></p>
			<p><b>%s</b>!</p>
			<p></p>
			<p>The budget for this years secret santa is around &pound;50 GBP</p>
			<p>...and remember, it's a secret!</p>
			<p></p>
			<p>Ho ho ho!</p>
			<p>Santa x</p>
		",
		explode( ' ', $oParticipant->getName() )[0],
		$oParticipant->getRecipient()->getName()
	);

	continue;
	$aResponse = $oMandrill->messages->send( $aParams );
	var_dump( $aResponse );
	echo '<hr />';
}


die();
$sHeaders = 'From: dave@davegoodchild.co.uk' . "\r\n" .
	'Reply-To: noreply@davegoodchild.co.uk' . "\r\n" .
	'X-Mailer: PHP/' . phpversion() . "\r\n" .
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=iso-8859-1' . "\r\n";


// $oTombola->addMessageTemplate(); // could use twig template formatting.

// $oTombola->validate(); // in case.

// could generate the matches in a temporary database
// then once confirmed (reviewed)...or a log output, just to be sure to be sure
