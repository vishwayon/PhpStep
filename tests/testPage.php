<?php
$matches;
if (preg_match_all('/\$F\{(\w{1,}|_)\}/', '$F{mat_rate} * $F{mat_qty}', $matches)) {
	foreach($matches[0] as $key => $match) {
		$token = new \stdClass() ;
		$token->text = $match;
		$token->attrs = [];
		$token->attrs[] = $matches[1][$key];
		echo json_encode($token) . PHP_EOL;
	}
} 
if (preg_match('/\$Each\{(\w{1,}|_),?\s{0,}(\d)?\}/', '$Each{mat_tran, 3}', $matches)) {
        echo json_encode($matches) . PHP_EOL;
	$token = new \stdClass() ;
	$token->text = $matches[0];
        $token->attrs = [];
	$token->attrs[] = $matches[1];
	if (isset($matches[2])) {
            $token->attrs[] = intval($matches[2]);
	}
	//$cn->tokens[] = $token;
	echo json_encode($token) . PHP_EOL;
	
}