<?php

/*
 *
 *  _____            _               _____           
 * / ____|          (_)             |  __ \          
 *| |  __  ___ _ __  _ ___ _   _ ___| |__) | __ ___  
 *| | |_ |/ _ \ '_ \| / __| | | / __|  ___/ '__/ _ \ 
 *| |__| |  __/ | | | \__ \ |_| \__ \ |   | | | (_) |
 * \_____|\___|_| |_|_|___/\__, |___/_|   |_|  \___/ 
 *                         __/ |                    
 *                        |___/                     
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author GenisysPro
 * @link https://github.com/GenisysPro/GenisysPro
 *
 *
*/

namespace pocketmine\network\protocol;
#include <rules/DataPacket.h>
use pocketmine\network\NetworkSession;
class LoginPacket extends DataPacket{
	const NETWORK_ID = Info::LOGIN_PACKET;
	const EDITION_POCKET = 0;
	public $username;
	public $protocol;
	public $gameEdition;
	public $clientUUID;
	public $clientId;
	public $identityPublicKey;
	public $serverAddress;
	public $skinId;
	public $skin = "";
	public $clientData = [];
	public function canBeSentBeforeLogin() : bool{
		return true;
	}
	public function decode(){
		$this->protocol = $this->getInt();
		if($this->protocol !== Info::CURRENT_PROTOCOL){
			$this->buffer = null;
			return; //Do not attempt to decode for non-accepted protocols
		}
		$this->gameEdition = $this->getByte();
		$this->setBuffer($this->getString(), 0);
		$chainData = json_decode($this->get($this->getLInt()));
		foreach($chainData->{"chain"} as $chain){
			$webtoken = $this->decodeToken($chain);
			if(isset($webtoken["extraData"])){
				if(isset($webtoken["extraData"]["displayName"])){
					$this->username = $webtoken["extraData"]["displayName"];
				}
				if(isset($webtoken["extraData"]["identity"])){
					$this->clientUUID = $webtoken["extraData"]["identity"];
				}
				if(isset($webtoken["identityPublicKey"])){
					$this->identityPublicKey = $webtoken["identityPublicKey"];
				}
			}
		}
		$this->clientData = $this->decodeToken($this->get($this->getLInt()));
		$this->clientId = $this->clientData["ClientRandomId"] ?? null;
		$this->serverAddress = $this->clientData["ServerAddress"] ?? null;
		$this->skinId = $this->clientData["SkinId"] ?? null;
		if(isset($this->clientData["SkinData"])){
			$this->skin = base64_decode($this->clientData["SkinData"]);
		}
	}
	public function encode(){
		//TODO
	}
	public function decodeToken($token){
		$tokens = explode(".", $token);
		list($headB64, $payloadB64, $sigB64) = $tokens;
}
