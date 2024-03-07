<?php /* 
Divide a card pile into multiple ones without creating new categories.
edit 03/07/2024: optimized code & added ability to show piles as text 

function sortmypile($tcg, $category, $start = '', $end = '', $doubles = 0, text = false)
	$tcg = the name of the TCG as defined in the database; $category = the card category to display;
	$start = the value you want to start with; $end = the value you want to end with; 
	$doubles = 0 Show all, 1 show uniques only, 2 Show Doubles only
 	$text = display as images w undertext (false) or as plaintext (true)

Function originally by Rizu from http://tcg.haltfate.org/stuff.php
MODDED - Shows undertext via Joey's Mod (No Pending in Keeping!) from https://tcg.hopeful-despair.net/showtext.php
All code belongs to RIZU and JOEY - I just modified it so it all works together.

ADD THIS TO THE END OF YOUR MODS.PHP file */

function sortmypile($tcg, $category, $start = '', $end = '', $doubles = 0, $text = false) {
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	
	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $tcginfo['id'];
	$cardsurl = $tcginfo['cardsurl'];
	$format = $tcginfo['format'];
	$altname = strtolower(str_replace(' ','',$tcg)); // show undertext mod
	
	$category = $sanitize->for_db($category);
	$cards = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$category' LIMIT 1");
	
	$start = $sanitize->for_db($start);
	$start = strtoupper($start);
	$end = $sanitize->for_db($end);
	$end = strtoupper($end);
	$searchme = '/['.$start.'-'.$end.']/i';
	
	if($cards === '') {
		 echo '<p class="cards"><em>There are currently no cards under this category.</em></p>'; 
	} else {
		$cardsall = explode(', ',$cards); // explode the cards string
		sort($cardsall); // sort cards
		$array_count = count($cards);	// count the number of cards?? idk why this is here, it was in Rizu's OG code. Maybe for Combined Worth?

		$cardsall = array_map(trim, $cardsall);	// all cards		
		$cardsuni = array_unique($cardsall); // all unique/first cards
		$cardsdou = array_diff_assoc($cardsall, array_unique($cardsall)); // all doubles

		// check what cards were using
		$cardsInPlay = array();	
		if( $doubles == 0 ) { $cardsInPlay = $cardsall;} 
    		elseif( $doubles == 1) { $cardsInPlay = $cardsuni; }
    		elseif( $doubles == 2) {  $cardsInPlay = $cardsdou; }

		echo "<ul class=\"list-inline\">";
  		foreach ( $cardsInPlay as $card ) { 
  			$card = trim($card); 
  			if($card != '') { 
  				if ( $text == true ) { echo $card.', '; }
  				else { echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>'; }
  			}
  		} 
  		echo "</ul>";
  		unset($card);
	} 
}

?>

<!-- Add this to your Stylesheet -->
<style>
#cardlist li {
    display: inline-block;
}
.cardname { line-height: 25px;
    width: 115px;
    display: block;
    font-size: 11px;
    margin: 4px;
  }
</style>

