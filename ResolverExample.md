# Basic Query Syntax #

### Get the MX Records for google.com ###

```

//
// create new resolver object, passing in an array of name
// servers to use for lookups
//
$r = new Net_DNS2_Resolver(array('nameservers' => array('192.168.0.1')));

//
// execute the query request for the google.com MX servers
//
try {
        $result = $r->query('google.com', 'MX');
        
} catch(Net_DNS2_Exception $e) {
	
	echo "::query() failed: ", $e->getMessage(), "\n";
}

//
// loop through the answer, printing out the MX servers retured.
//
foreach($result->answer as $mxrr)
{
        printf("preference=%d, host=%s\n", $mxrr->preference, $mxrr->exchange);
}

```

### Zone Transfer (AXFR) for example.com ###

```

//
// create new resolver object, passing in an array of name
// servers to use for lookups
//
$r = new Net_DNS2_Resolver(array('nameservers' => array('192.168.0.1')));

//
// add a TSIG to authenticate the request
//
$r->signTSIG('mykey', '9dnf93asdf39fs');

//
// execute the query request for the google.com MX servers
//
try {
        $result = $r->query('example.com', 'AXFR');
        
} catch(Net_DNS2_Exception $e) {
	
	echo "::query() failed: ", $e->getMessage(), "\n";
}

//
// loop through the answer, printing out each resource record.
//
foreach($result->answer as $rr)
{
        echo $rr;
}

```