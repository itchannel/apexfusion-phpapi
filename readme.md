# Rough PHP Apexfusion Project!

Used it for my own project but thought it might help a few people. 

Code needs cleaning and optimising still WIP.

### TODO
* Add alarm log
* Add previous probe statistics
* Add configuration viewing
* Add raw output arrays (Allow for getting variables from all data retrieved)

### Below is sample code to hook into the PHP Class File

```
<?php
include "apex.php";
//Initate Class
$apex = new Apex();
//Set Credential Variables for apexfusion.com
$apex->username = "example";
$apex->password = "password123";
$apex->apexdevid = "GUID";

//Run function to retrieve info from apexfusion.com
$apex->apexstatus();

//Then can be used like the following

//Temp Probe
echo $apex->apextmp;
//PH Probe
echo $apex->apexph;

//Apex Log for current day as array
foreach ($apex->apexlog as $logentry)
{
  //Status of log entry
  echo $logentry->status;
  //Lookup and print device name from log
  echo $apex->devices[$log->did];
}
?>
```
