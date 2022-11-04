# protector
php script to ban by frequently requests

usage in index.php before any code add

```
include_once('protector.php');
```

About - in folder /ips/ create files IP.txt which contain IP, counter of requests and timestamp.
If requests more than maximum, user will be banned by interruption of php script and IP
will be added to file 'blacklist.txt'.
After timeout is going user will be unbanned.
