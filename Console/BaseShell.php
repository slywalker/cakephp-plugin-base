<?php
App::uses('Shell', 'Console');

class BaseShell extends Shell {

	public function progressBar($current, $total, $size = 50) {
		$perc = intval(($current / $total) * 100);
		for ($i = strlen($perc); $i <= 4; $i++) {
			$perc = ' ' . $perc;
		}
		$total_size = $size + $i + 3;

		if ($current > 0) {
			for ($place = $total_size; $place > 0; $place--) {
				echo "\x08";
			}
		}

		for ($place = 0; $place <= $size; $place++) {
			if ($place <= ($current / $total * $size)) {
				echo "\033[42m \033[0m";
			} else {
				echo "\033[47m \033[0m";
			}
		}

		echo " $perc%";

		if ($current == $total) {
			echo PHP_EOL;
		}
	}

}