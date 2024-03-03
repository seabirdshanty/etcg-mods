<?php
// sort collecting by muffy
// will show collecting decks based on their sort number. quick and easy
// add this to the end of your MODS.PHP file

function sort_collecting($tcg, $sort = '') {
	
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	
	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $tcginfo['id'];
	$cardsurl = $tcginfo['cardsurl'];
	$format = $tcginfo['format'];
	
	$sort = intval($sort); 

	$result = $database->query("SELECT * FROM `collecting` WHERE `tcg` = '$tcgid' AND `mastered` = '0' AND `sort` = '$sort' ORDER BY `worth`, `deck`"); 

	while ( $row = mysqli_fetch_assoc($result) ) { 
		$cards = explode(',',$row['cards']);
		if ( $row['format'] != 'default' ) { $format = $row['format']; }
			
		array_walk($cards, 'trim_value');
		
		if ( $row['cards'] == '' ) { $count = 0; } else { $count = count($cards); }
		?>
		<section class="card_col <?php echo $row['deck']; ?>">
        <p align="center">
        	<?php
				for ( $i = 1; $i <= $row['count']; $i++ ) {
					
					$number = $i;
					if ( $number < 10 ) { $number = "0$number"; }
					$card = "".$row['deck']."$number";
					
					$pending = $database->num_rows("SELECT * FROM `trades` WHERE `tcg`='$tcgid' AND `receiving` LIKE '%$card%'");
					
					if ( in_array($card, $cards) ) echo '<img src="'.$tcginfo['cardsurl'].''.$card.'.'.$format.'" alt="" title="'.$card.'" />';
					else if ( $pending > 0 ) { echo '<img src="'.$tcginfo['cardsurl'].''.$row['pending'].'.'.$format.'" alt="" title="'.$card.'" />'; }
					else { echo '<img src="'.$tcginfo['cardsurl'].''.$row['filler'].'.'.$format.'" alt="" />'; }
					
					if ( $row['puzzle'] == 0 ) { echo ' '; }
					if ( $row['break'] !== '0' && $i % $row['break'] == 0 ) { echo '<br />'; }
					
				}
			?>
        </p>
        <h2><?php echo $row['deck']; ?> (<?php echo $count; ?>/<?php echo $row['count']; ?>)</h2>
        </section>
        
        <?php 
	}
	
}
?>
