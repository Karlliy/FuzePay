rem ******MySQL backup start******

::刪除一週前的備份資料
forfiles /p "D:\\backup" /m Wise_BD_backup_*.sql -d -1 /c "cmd /c del /f @path"
@echo off
::設定時間變數
set "Ymd=%date:~5,2%%date:~8,2%%date:~11,2%%time:~0,2%%time:~3,2%%time:~6,2%"

::進入mysql安裝目錄的bin目錄下
cd C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\

::執行備份操作
::mysqldump -uroot -padpay1688! -A --default-character-set=utf8 > D:\\backup\\anda_BD_backup_%Ymd%.sql
mysqldump -ubackup -p9gzXfuESXdOFuWc -A --default-character-set=utf8 > D:\\backup\\Wise_BD_backup_%Ymd%.sql
@echo on
::pause
rem ******MySQL backup end******