@echo off

echo RML Deploy

if not exist ..\..\..\ftp_password.txt (
echo Can not find FTP password file for server.
echo create password file in ..\..\..\ftp_password.txt
goto error
)

set /p ftppassword=<..\..\..\ftp_password.txt

call make_zip_release.bat
if not %bank_src_error_code% == 0 goto error

echo From make_zip_release batch file:
echo Edition: '%bank_src_edition%'
echo Edition label: '%bank_src_edition_label%'
echo Version: '%bank_src_version%'
echo Version label: '%bitcoin_bank_src_version_label%'
echo Release: '%bitcoin_bank_src_release%'
echo Release type: '%bitcoin_bank_src_release_type%'
echo Version name: '%bitcoin_bank_src_version_name%'
echo Package name: '%bitcoin_bank_src_package_name%'
echo Zip file name: '%bitcoin_bank_src_zip_file_name%'
echo Output directory: '%bitcoin_bank_src_output_path%'
echo Zip file path: '%bitcoin_bank_src_zip_file_path%'
echo Zip file list: '%bitcoin_bank_src_zip_file_list%'


echo Deploy to readmorelogin.com
echo Zip package: %bitcoin_bank_src_zip_file_path%

echo Get date
echo Computer date: %date%
echo %date%| findstr /r "^[0-9.]*$">nul
if not %errorlevel%==0 (
    echo Date format not as expected, must have dot delimiter
    goto error
)

for /f "tokens=1-3 delims=." %%a in ("%date%") do (
    set mydate=%%c-%%a-%%b
    set year=%%c
)
if %year% lss 2020 (
    echo Date format wrong order.
    goto error
)

echo Date: %mydate%


set latest_version_file=%bitcoin_bank_src_output_path%latest-%bitcoin_bank_src_edition%-%bitcoin_bank_src_release_type%.txt
echo Latest version info file: %latest_version_file%

echo Edition name: %bitcoin_bank_src_edition_label%>%latest_version_file%
echo Version: %bitcoin_bank_src_version_label%>>%latest_version_file%
echo WP tested: %bitcoin_bank_src_wp_tested%>>%latest_version_file%
echo Download file: %bitcoin_bank_src_zip_file_name%>>%latest_version_file%
echo Added date: %mydate%>>%latest_version_file%

echo ---
type %latest_version_file%
echo ---

REM Generate the script. Will overwrite any existing file
set script_file=%temp%\readmorelogin-ftp-script.tmp
echo FTP script file: %script_file%

echo cd download>%script_file%
echo put %bitcoin_bank_src_zip_file_path%>>%script_file%
echo put %latest_version_file%>>%script_file%
echo quit>>%script_file%

psftp readmorelogin.com@ssh.readmorelogin.com -pw %ftppassword% -b %script_file%
if not %errorlevel%==0 goto ftp_error

del %script_file%

goto ok

:ftp_error
echo FTP upload failed.
goto error

:ok
echo OK
set bitcoin_bank_src_error_code=0
goto end

:error
echo ERROR
set bitcoin_bank_src_error_code=1
goto end

:end
