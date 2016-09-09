<?php
require_once(__DIR__ . "/class.domainparser.php");

$begin_mem = microtime(true);
$last_mem = false;

$trace_count = 1;
function trace($msg)
{
	global $trace_count, $begin_time, $last_time;

	$cur_time = microtime(true);
	if ($last_time === false)
	{
		$last_time = $cur_time;
	}
	echo $trace_count++ . ": [" . floor(($cur_time - $begin_time) * 10000) / 10000 . " +" . floor(($cur_time - $last_time) * 10000) / 10000 . "] " . $msg . "<br>";
	$last_time = $cur_time;
}

function trace_mem($msg = '')
{
	global $begin_mem, $last_mem;

	$cur_mem = memory_get_usage();
	if ($last_mem === false)
	{
		$last_mem = $cur_mem;
	}
	echo "[" . ($cur_mem - $begin_mem) . " +" . ($cur_mem - $last_mem) . "] " . $msg . "<br>";
	$last_mem = $cur_mem;
}


$cnt = isset($_GET['cnt']) ? $_GET['cnt'] : 1;

//$raw_url = '//' . parse_url($raw_url2, PHP_URL_HOST);



$test_urls = "
cpa.trafmag.gdn
tmp.fs.ua
http://www.yves-rocher.ua/control/product/~category_id=2154/~pcategory=2000/~product_id=W11497?cmSrc=Category&admitad_uid=ace7033e7ec5a560f12773a5834b4cb7
user:pass@www.pref.okinawa.jp:8080/path/to/page.html?query=string#fragment
wdddd.blogspot.ac.com.ba.blogspot.ca
рывоа-sdfbksdf.sdmfkldmf.sdfnf.wdddd.blogspot
ba.blogspot.ca
ba.blogspot.ca.ke
aksdfnaskl.ntu-kpi.kiev.ua
www
www.ck
s1.trafmag.com
www.ru
registered.com
sub.registered.com
parliament.uk
sub.registered.valid.uk
registered.somedom.kyoto.jp
invalid-fqdn
org
academy.museum
sub.academy.museum
subsub.sub.academy.museum
sub.nic.pa
registered.sb
sub.registered.sb
subsub.registered.something.zw
subsub.registered.9.bg
registered.co.bi
sub.registered.bi
subsub.registered.ee
s.tld
www.ru
fff.www.ru/
патпоап.ijn.hfhhf.bd
патпоап.ijn.hfhhf.bd
патпоап.ijn.hfhhf.bd
1.
http://www.waxaudio.com.au/audio/albums/the_mashening
example.COM
giant.yyyy
cea-law.co.il
http://edition.cnn.com/WORLD/
http://en.wikipedia.org/
a.b.c.mm
https://test.k12.ak.us
www.scottwills.co.uk
b.ide.kyoto.jp
a.b.example.uk.com
test.nic.ar
a.b.test.ck
baez.songfest.om
politics.news.omanpost.om
us.example.com
us.example.na
www.example.us.na 
us.example.org
webhop.broken.biz
www.broken.webhop.biz
//www.broken.webhop.biz
ftp://www.waxaudio.com.au/audio/albums/the_mashening
ftps://test.k12.ak.us
fake-scheme+RFC-3986.compliant://example.com
http://localhost
test.museum
bob.smith.name
tons.of.info
http://Яндекс.РФ
食狮.com.cn
www.xn--85x722f.xn--fiqs8s
xn--85x722f.com.cn
http://[::1]/
http://[2001:db8:85a3:8d3:1319:8a2e:370:7348]/
https://[2001:db8:85a3:8d3:1319:8a2e:370:7348]:443/
http://192.168.1.2/
http://127.0.0.1:443
http://67.196.2.34/whois-archive/latest.php?page=2479
http://[fe80::3%25eth0]
http://[fe80::1%2511]
http://www.example.dev
http://example.faketld
";

$test_urls = explode("\n", $test_urls);
$urls_cnt = count($test_urls);

trace_mem();
$dp = new DomainParser();
trace("Started ($cnt)");
for ($i = 0; $i < $cnt; $i++)
{
	$url = $dp->getRegisteredDomain($test_urls[$i % $urls_cnt]);
}
trace("Ended");
trace_mem();


echo "\n\n\n<br><br><br>\n\n\n" . '<table border=1><th></th><th>domain</th>';
foreach ($test_urls as $i => $url)
{
	$url = clear_url($url);
	if (empty($url)) continue;
	$url = '//' . parse_url('//' . $url, PHP_URL_HOST);

	echo '<tr>' .
			'<td>' . $url . '</td>' .
			'<td>' . $dp->getRegisteredDomain($url) . '</td>' .
		'</tr>';
}
echo '</table>';


?>
<style>
	table {
		border-collapse: collapse; border: solid 1px #ccc;
	}
	td, th {
		padding: 10px;
	}
</style>
