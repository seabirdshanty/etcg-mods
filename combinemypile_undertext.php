<?php /* 
Combines Rizu's combinemypile with Joey's showundertext, as well as providing to show the entire combined pile as text
 
Rizus Text:
  Combine up to 5 categories into one pile.
  $tcg = the name of the TCG as defined in the database.
  $cata, $catb, $catc, $catd, $cate = the categories you want to combine. a and b are obligatory. 
  $doubles = 1 divide pile into uniques/doubles; 2 shows only unique cards; 3 shows only doubles.

Use As:
combinemypile_ut($tcg, $doubles, $cata, $catb, $catc, $catd, $cate, $text);

Example: I want to combine my aria decks
combinemypile( 'tcgname', '', 'aria1', 'aria2', 'aria3')

Example 2: I want tocmbine all my keeps pile into 1 and display it as text
combinemypile_ut( 'tcgname', '', 'keeps_high', 'keeps_med', 'keeps_low', , true)

All code belongs to RIZU and JOEY - I just modified & optimized it so it all works together.
ADD TO THE END OF YOUR MODS.PHP FILE

*/
function combinemypile_ut($tcg, $doubles = 0, $cata, $catb, $catc = '', $catd = '', $cate = '', $text = false) {
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	
	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $tcginfo['id'];
	$cardsurl = $tcginfo['cardsurl'];
	$format = $tcginfo['format'];
	
	$cata = $sanitize->for_db($cata); $catb = $sanitize->for_db($catb); $catc = $sanitize->for_db($catc); $catd = $sanitize->for_db($catd); $cate = $sanitize->for_db($cate);
	$cardsa = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$cata' LIMIT 1");
	$cardsb = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catb' LIMIT 1");
	if($catc === '') { $cardsc = ''; } else { $cardsc = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catc' LIMIT 1"); $cardsc = $cardsc['cards']; }
	if($catd === '') { $cardsd = ''; } else { $cardsd = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catd' LIMIT 1"); $cardsd = $cardsd['cards']; }
	if($cate === '') { $cardse = ''; } else { $cardse = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$cate' LIMIT 1"); $cardse = $cardse['cards']; }
	$cards = $cardsa['cards'] . ', ' . $cardsb['cards'] . ', ' . $cardsc . ', ' . $cardsd . ', ' . $cardse;
	
	if ( $cards === ', , , ,' ) { 
		echo '<p class="cards"><em>There are currently no cards under this category.</em></p>'; 
	} 
	else {
		$cardsall = explode(', ',$cards);
		sort($cardsall);
		$cardsall = array_map(trim, $cardsall);			
		$cardsuni = array_unique($cardsall);
		$cardsdou = array_diff_assoc($cardsall, array_unique($cardsall));

		// init an array for cards that are in play
		$cardsInPlay = array();	
		if( $doubles == 0) {
			// no doubles? no problem ;3
			$cardsInPlay = $cardsall;
		} else {
			// use modulous to find out if you need 1, 2 or 3
			if ( $doubles % 2 == 0 ) { $cardsInPlay = $cardsuni; } // for 2
			else { $cardsInPlay = $cardsdou; } // for 1 or 3
		}

		echo "<ul class=\"list-inline\">";
		foreach ( $cardsInPlay as $card ) { 
			$card = trim($card); if($card != '') { 
				if ( $text == true ) { echo $card.', '; }
				else {
					echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>';
				}
			}
		} 
		echo "</ul>";

		// unset foreach variable or deal w a memory leak!
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
