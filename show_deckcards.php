<?php 

// This shows cards from a specific deck within a specific pile *that is not collecting*
// ie show ONLY shineinouzen cards from your futures pile, chii from your trades pile ect.
// this is so you dont go nuts having a ton of collecting decks, or showing off cards for your valentines pairings :3
function show_deckcards($tcg, $category = '', $deckname = '') {
	
	// get ready to grab form the database
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	
	// grab what you need from mySQL to print cards later on
	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $tcginfo['id'];
	$cardsurl = $tcginfo['cardsurl'];
	$format = $tcginfo['format'];
	
	// sanitize the deckname
	$deckname =  $sanitize->for_db($deckname); 

	// grab what you need from the database. we need cards from our tcg from the requested category
	$result = $database->get_assoc("SELECT `cards` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$category' LIMIT 1");

	// make a new array to push to
	$printMe = array();

  // dig through the cards array
	foreach ($cards as &$card) {
		// push each card with the deckname to a different array
		if ( strpos($card,$deckname) !== false ) {
			array_push( $printMe, $card);
		}
	}
	// unset foreach variable. if you dont do this it'll cause a memory leak.
	unset($card);

	// grab the size of your card array
	$myCards = sizeOf($printMe);

	if ( $myCards == 0 ) {
		// if no cards, print a message :3
		echo "<i>Nothing here but us chickens!</i><br/>";
	} else {
		foreach( $printMe as &$card ) {
			// otherwise print yer cards!
			echo '<img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /> ';
		}
	}
} 


?>
