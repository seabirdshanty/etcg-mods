<?php 

/*
This is show_deckcards.php with an addition:
it appends extra filler cards as if it was a collecting deck depending on the number you put

I.E. say you have a Pairings deck, you need 10 of each character.
You would put all of your cards into a "valentines" pile
and pull shineinouzen and vladilenamilize for a "deck" display

You would use
  show_minideck('sakura','valentines','shineinouzen',10); echo "<br />";
  show_minideck('sakura','valentines','vladilena',10);

To show the "deck" with filler cards showing how many you need left on your tradepost.

The while() has a break # of 99, incase of errors. You can modifiy this number as needed

Have Fun!
-Muffy

*/
function show_minideck($tcg, $category = '', $deckname = '', $deckSize = '') {

  // filename of your filler card
  // it MUST be in the same folder as all your other cards!
  $fillerCard = "filler.png";
	
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

	// grab what you need from the database. we need cards from our tcg from the requested category.
	$result = $database->get_assoc("SELECT `cards` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$category' LIMIT 1");

	// make a new array to push to!
	$printMe = array();

	// turn the cards results into an array!
	$cards = explode(', ',$result['cards']);

	// dig through the cards array!
	foreach ($cards as &$card) {
		// push each card with the deckname to a different array!
		if ( strpos($card,$deckname) !== false ) {
			array_push( $printMe, $card);
		}
	}
	// unset foreach variable. if you dont do this it'll cause a memory leak.
	unset($card);

	// grab the size of your card array!
	$myCards = sizeOf($printMe);
	$i = 0; // counter

	if ( $myCards == 0 ) {
		// if no cards, print a message :3
		echo "<i>Nothing here but us chickens!</i><br/>";
	}
	else {
		while ($i < $deckSize) {
			if ( $i < $myCards ) { // if the number matches a value that has a card in it, print the card
				echo '<img src="'.$cardsurl.''.$printMe[$i].'.'.$format.'" alt="'.$printMe[$i].'" title="'.$printMe[$i].'" /> ';
			} else { // otherwise, just show a filler image
				echo "<img src='".$cardsurl.$fillerCard."' />";
			}
			$i++;
			if( $i > 99 ) { break; } // allow a break just in case something goes arwy
		}
	}
} 

?>
