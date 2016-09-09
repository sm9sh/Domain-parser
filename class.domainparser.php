<?php

// “Æ” <== for utf-8 autodetection

function clear_url($url, $clear_last_slash = false)
{
	$url = trim($url);

	if (mb_substr($url, 0, 7) == 'http://')
	{
		$url = mb_substr($url, 7, mb_strlen($url));
	}
	else
	if (mb_substr($url, 0, 8) == 'https://')
	{
		$url = mb_substr($url, 8, mb_strlen($url));
	}
	else
	if (mb_substr($url, 0, 2) == '//')
	{
		$url = mb_substr($url, 2, mb_strlen($url));
	}

	if (mb_substr($url, 0, 4) == 'www.')
	{
		$url = mb_substr($url, 4, mb_strlen($url));
	}

	while ($url != '' && ($url[mb_strlen($url) - 1] == '/' && $clear_last_slash || $url[mb_strlen($url) - 1] == "\n" || $url[mb_strlen($url) - 1] == "\r"))
	{
		$url = mb_substr($url, 0, mb_strlen($url) - 1);
	}

	return $url;
}

//========================================================================================
// Domain parser for finding registered domain name (www.subdomain1.subdomain2.example.com.ua => example.com.ua)
//========================================================================================
class DomainParser
{
	private $publicDomainList = false;
	private $php_cache_filename = "public-domain-list.php";
	private $txt_db_filename    = "public-domain-list.txt";
	private $call_cache = false;

	private function initialize()
	{
		if ($this->publicDomainList !== false && is_array($this->publicDomainList))
		{
			return true;
		}

		$path = $this->php_cache_filename;
		if (!file_exists($path))
		{
			return false;
		}

		$data = require($path);

		if (is_array($data))
		{
			$this->publicDomainList = $data;
		}

		return true;
	}

	public function getRegisteredDomain($url)
	{
		$url = clear_url($url);
		$host = $host_orig = mb_strtolower(parse_url('//' . $url, PHP_URL_HOST));

		if (strpos($host, '.') === false || strpos($host, '#') !== false)
		{
			return false;
		}

		if (is_array($this->call_cache) && isset($this->call_cache[$host_orig]))
		{
			return $this->call_cache[$host_orig];
		}

		$this->initialize();

		if (empty($this->publicDomainList) || isset($this->publicDomainList[$host]))
		{
			return false;
		}

		$res = false;
		$depth = substr_count($host , '.');
		while ($host && $depth-- > 0)
		{
			$pos = strpos($host, '.', 1);

			if ($pos === false)
			{
				return false;
			}

			$host_cutted = substr($host, $pos + 1);
			if (isset($this->publicDomainList[$host_cutted]))
			{
				$res = $host;
				break;
			}
			else
			if (isset($this->publicDomainList['*.' . $host_cutted]))
			{
				if (preg_match("#([^\.]+.$host)$#u", $host_orig, $matches))
				{
					$res = $matches[0];
					break;
				}
			}
			$host = $host_cutted;
		}

		if ($this->call_cache === false)
		{
			$this->call_cache = array();
		}
		$this->call_cache[$host_orig] = $res;

		return $res;
	}

	public function updatePublicDomainList($domains_list_url = 'https://publicsuffix.org/list/public_suffix_list.dat')
	{
		$domains = array();

		$content = file_get_contents($domains_list_url);
		$result = file_put_contents($this->txt_db_filename, $content);
		if ($result === false)
		{
			exit("Can't write '{$this->txt_db_filename}'");
		}
		$content =  explode("\n", $content);

		foreach ($content as $line)
		{
			$line = trim($line);
			if ($line == '' || substr($line, 0, 2) == '//')
			{
				continue;
			}
			$domains[$line] = 1;
		}

		if (count($domains) === 0)
		{
			exit('Input array of lines does not have any valid suffix, check input');
		}

		$data = '<?php' . PHP_EOL . 'return' . PHP_EOL . var_export($domains, true) . ';';
		$result = file_put_contents($this->php_cache_filename, $data);

		if ($result === false)
		{
			exit("Can't write '{$this->php_cache_filename}'");
		}

		echo "Successfully updated '$this->txt_db_filename' and '$this->php_cache_filename' (" . count($domains) . " items)";
	}

}
