<?php

namespace helpers;

class RequestSender
{
	/**
	 * @param $url
	 * @param array $data
	 * @param string $method
	 * @return array
	 */
	public static function sendHttpRequest($url, array $data = [], string $method = 'GET'): array
	{
		$curl = curl_init();

		switch (strtoupper($method)) {
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				break;
			case 'GET':
				if (!empty($data)) {
					$url = sprintf("%s?%s", $url, http_build_query($data));
				}
				break;
			// другие методы при необходимости
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);

		$response = json_decode(curl_exec($curl), true);

		if (curl_errno($curl)) {
			$error = curl_error($curl);
			curl_close($curl);
			return ['error' => true, 'message' => $error];
		} else {
			curl_close($curl);
			return ['error' => false, 'response' => $response];
		}
	}
}