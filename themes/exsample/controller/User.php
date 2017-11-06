<?php

class user {
	
	public function listAction() {

	}

	public function detailAction( $args ) {

	    echo  __CLASS__ . "::" . __METHOD__;
	    var_dump($args);

	}
}