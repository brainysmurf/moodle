To install stuff needed to connect to MSSQL from PHP:
```
sudo apt-get install php5-odbc php5-sybase tdsodbc
```

Then:
```
vi /etc/freetds/freetds.conf
```

And add:
```
[global]
tds version = 8.0
client charset = UTF-8
```
(Source: http://www.robertprice.co.uk/robblog/2013/01/using-sql-server-ntext-columns-in-php/)
