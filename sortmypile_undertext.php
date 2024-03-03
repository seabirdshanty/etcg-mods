<?php

// Divide a card pile into multiple ones without creating new categories.
// $tcg = the name of the TCG as defined in the database; $category = the card category to display;
// $start = the value you want to start with; $end = the value you want to end with; $doubles = divide pile into uniques + doubles
// MODDED - Shows undertext via Joey's Mod (No Pending in Keeping!) from https://tcg.hopeful-despair.net/showtext.php
// All code belongs to RIZU and JOEY - I just modified it so it all works together.
// add this to the end of you MODS.PHP file

function sortmypile($tcg, $category, $start = '', $end = '', $doubles = 0) {
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
		$cards = explode(',',$cards['cards']);
		$cards = array_map(trim, $cards);
		$array_count = count($cards);
		echo "<ul class=\"list-inline\">";
		if($doubles > 0) {
			$cardsuni = array_unique($cards);
			$cardsdou = array_diff_assoc($cards, array_unique($cards));
			foreach( $cardsuni as $card ) {
				$card = trim($card);
				if(preg_match("$searchme", $card[0])){ 
					echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>';
				} 
			}
			foreach( $cardsdou as $cardd ) {
				$cardd = trim($cardd);
				if(preg_match($searchme, $cardd[0])){ 
					echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>';
				} 
			}
		} else {
			foreach( $cards as $card ) {
				$card = trim($card);
				if(preg_match($searchme, $card[0])){ 
					echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>';
				} 
			}
			//echo '</p>';
			
		} 
		echo "</ul>";
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

<!-- to display -->
<ul id="cardlist"><?php show_undertext('TCG','category'); ?></ul>

