<?php 
define('VALID_INC', TRUE); include_once 'func.php';
/* 
==============================================
    Card Sort via category
    by Muffy
==============================================

Sorts cards for you.
Place cards into the "sortme" category, then refresh the page for all of these cards to be sorted for you :3
One day this will allow the log input that the idolise cardsort does.
(the coding of that confused me, so i coded my own)

-------------
HOW TO USE:
-------------
1. Place this file in the same folder as your TCG.
2. Create a category in eTCG called "sortme" (or whatever you prefer, you can change this in config below) 
  - with a cardworth of 0 
  - no autoupload
3. While you're proccessing your logs, put cards you want to sort into this category.
4. Refresh this page, and the cards will be sorted into categories for you.
5. Once your sorting is complete, pull all your cards out of the sortme category.
6. Rinse and Repeat!

This is easier to me, that way i process my cards while logging them all
Unfortunately this doesn't allow for external input, or checks for EXACT cards, just what decks are where.

-------------
Func Calls
-------------
NOTE: This is it's own standalone page, reading database info from your func.php. 
Make sure none of the function calls interact with any of your mods.

grabCats()
callCollect()
catCards()
deckArray()
cardArray()
replaceNumber()

==============================================
CONFIG
============================================== */

// name of the TCG you're working with
$workTCG = "tcgnamehere";

// name of the Sorting category, default "sortme"
$mySortCat = "sortme";

/* ==============================================
        Do not touch anything below 
        lest you know what youre doing.
 ============================================== */

$workArray = array();
$inputArrays = array();

function grabCats( $tcg ) {
	// grabs categories from the database
	$holdCats = array(); //categories

	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);

	$result = $database->get_assoc("SELECT `id` FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $result['id'];

	$counter = 1;

	// grabs each category name from the database
	while ($counter > 0) {
		$result = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `id`='$counter' ");
		$pushMe = $result['category'];
		// if the name is empty, dont push
		if($pushMe == "") { 
			// check next value
			$checkIt = $counter + 1;
		    $wonder = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `id`='$checkIt' ");
			$wonder = $wonder['category'];
			// if THAT's empty, break
			if($wonder == "") {
				unset( $wonder, $pushMe, $result );
				break; 
			}
		} else {
			// push otherwise
			array_push($holdCats, $pushMe);
		}
		
		$counter++;
	}

	// get cats :3
	return $holdCats;
	
}

// grab cards from collecting
function callCollect( $tcg ) {
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);

	$result = $database->get_assoc("SELECT `id` FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $result['id'];

	$result = $database->query("SELECT * FROM `collecting` WHERE `tcg` = '$tcgid' AND `mastered` = '0' ORDER BY `sort`, `deck`");
	$cards = '';

	while ($row = mysqli_fetch_assoc($result)) {	
		$current = explode(', ', $row[cards]);
		if($row['count'] == count($current)){

		} 
		else {
		$cards .= $row['cards'] . ', ';
		$total[$row['deck']] = $row['count'];
		}
	}

	return $cards;
}

// grab cards from categories
function catCards( $tcg, $category, $worth = '') {
	
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	$category = $sanitize->for_db($category);

	$result = $database->get_assoc("SELECT `id` FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $result['id'];

	 $result = $database->get_assoc("SELECT `cards` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$category' LIMIT 1");
	return $result['cards'];
}

// make a deck array
function deckArray ( string $inputCards ) {
	$exportArray = array();

	// explode each card
	$inputArray = explode(', ', $inputCards );

	// remove duplicates
	$inputArray = array_values(array_unique($inputArray));

	//push to return array
	foreach ( $inputArray as &$value ) {
		array_push($exportArray, $value);
	}

	// unset foreach
	unset( $value );
	
	// return!
	return $exportArray;
}

// make a card array
function cardArray ( string $inputCards ) {
	$exportArrayTwo = array();

	// explode each card
	$exportArrayTwo = explode(', ', $inputCards );

	// return!
	return $exportArrayTwo;
}


function replaceNumber( string $fixMe ) {
	// replaces numbers in a string uwu
	$result = "";
	$result = preg_replace('/[0-9]+/', '', $fixMe);
	return $result;
}


echo "<h2>Card Sort from the sortme Category</h2>";

// grab your input
$inputCards = catCards( $workTCG, $mySortCat );
echo "<p>Your input: " . $inputCards ."</p>";

$sayArray = cardArray($inputCards);

// grab your decks
$inputCardsTwo = replaceNumber($inputCards);
$sayArrayCheck = deckArray($inputCardsTwo);

// grab collecting
$colCards = callCollect( $workTCG );
$colCards = replaceNumber($colCards);
$colArray = deckArray($colCards);

// start your excludes
$excludeCol = array();
$excludeMe = array();

// check if your decks are found in collectiong
$foundArray = array_intersect( $colArray, $sayArrayCheck);

if(count($foundArray) > 0) {
	echo "<p><h3>Collecting</h3>";
	foreach ( $foundArray as $decksToFind) {
		// dig thru the found array
		foreach( $sayArray as $cardsToFind) {
			// dig thru the input array
			if( strpos($cardsToFind, $decksToFind ) !== false && $decksToFind !== "") {
				// if found, print!
				echo $cardsToFind . ", ";
				// push cards we've found to remove from leftovers
				array_push ($excludeCol, $cardsToFind);
				// push cards we've found to remove for extras
				array_push ($excludeMe, $cardsToFind);
			}
		}
	}
	echo "</p>";
}

// unset foreach
unset($cardsToFind, $decksToFind);

// lets grab our leftovers
$leftoverArray = array_diff( $sayArray, $excludeCol );

// create a new deckcheck array
$leftoverArrayCheckCards = implode(", ", $leftoverArray);
$leftoverArrayCheckCards = replaceNumber($leftoverArrayCheckCards);
$leftoverArrayCheck = deckArray($leftoverArrayCheckCards);

// find my categories
$myCategories = grabCats( $workTCG ); // grab your categories
$countMeIn = sizeof($myCategories);

// dig through categories
foreach( $myCategories as &$meow ) {
	if ($meow !== $mySortCat) {
		// grab cards
		$workingCards = catCards( $workTCG, $meow );
		if($workingCards !== "") {
			// remove numbers
			$workingCards = replaceNumber($workingCards);
			// create working array
			$workArray = deckArray($workingCards);
			// find DECK matches
			$foundArray = array_intersect($workArray, $leftoverArrayCheck);

			// match check
			if(count($foundArray) > 0) {
				echo "<p><h3>" . $meow . "</h3>";
				foreach ( $foundArray as $decksToFind) {
					// dig through the found array
					foreach( $leftoverArray as $cardsToFind) {
						// dig thru the input array
						if( strpos($cardsToFind, $decksToFind ) !== false && $decksToFind !== "") {
							// matches found, print!
							echo $cardsToFind . ", ";
							// push more cards weve found
							array_push ($excludeMe, $cardsToFind);
						}
					}
				}
				echo "</p>";
			}
		}
	}
}

unset($meow, $cardsToFind, $decksToFind);

$sayArraySize = sizeof($sayArray);
$excludeMeSize = sizeof($excludeMe);

if( $sayArraySize !== $excludeMeSize ) {
	echo "<h3>Unsorted Cards</h3>";
	$extraCards = array_diff($leftoverArray, $excludeMe);
	$extraCardsPrint = implode(", ", $extraCards);
	echo $extraCardsPrint;
}

?>
