<?php

class User extends Model
{
	const STATUSES = [
	  	'STATUS_BLOCK' => 0,
	  	'STATUS_ACTIVE' => 1
	];

    protected static ?string $table = 'users';
}