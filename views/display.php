<?php

if($forecast)
{
	echo '<div class="forecast">';
	foreach($forecast as $f)
	{
		echo '<div class="weather_item cond_'.$f['condition_code'].'"><div class="day">'.$f['day'].'</div><div class="condition">'.$f['condition'].'</div><div class="high">'.$f['high'].'&deg;</div><div class="low">'.$f['low'].'&deg;</div></div>';
	}
	echo '</div>';
}

?>