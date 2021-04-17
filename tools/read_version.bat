@echo off

echo Bitcoin Bank Version Reader

set bitcoin_bank_src_input_file=..\bitcoin-bank.php
echo Read php file: %bitcoin_bank_src_input_file%

for /f "tokens=2 delims=:" %%a in ('type %bitcoin_bank_src_input_file%^|find "Version:"') do (
  set bitcoin_bank_src_version=%%a
  goto :continue
)
:continue
echo Version: '%bitcoin_bank_src_version%'
rem Remove one character in front and end, these are spaces
set bitcoin_bank_src_version=%bitcoin_bank_src_version:~1%
set bitcoin_bank_src_version_label=%bitcoin_bank_src_version%
echo Version: '%bitcoin_bank_src_version%'

set bitcoin_bank_src_readme=..\readme.txt
echo Read readme file: %bitcoin_bank_src_readme%

for /f "tokens=2 delims=:" %%a in ('type %bitcoin_bank_src_readme%^|find "Tested up to:"') do (
  set bitcoin_bank_src_wp_tested=%%a
  goto :continue2
)
:continue2
echo WP Tested: '%bitcoin_bank_src_wp_tested%'
rem Remove one character in front and end, these are spaces
set bitcoin_bank_src_wp_tested=%bitcoin_bank_src_wp_tested:~1%
echo WP Tested: '%bitcoin_bank_src_wp_tested%'

set bitcoin_bank_src_version=%bitcoin_bank_src_version:A=a%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:B=b%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:C=c%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:D=d%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:E=e%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:F=f%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:G=g%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:H=h%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:I=i%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:J=j%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:K=k%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:L=l%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:M=m%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:N=n%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:O=o%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:P=p%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:Q=q%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:R=r%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:S=s%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:T=t%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:U=u%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:V=v%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:w=w%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:x=x%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:y=y%
set bitcoin_bank_src_version=%bitcoin_bank_src_version:z=z%

set bitcoin_bank_src_version_name=%bitcoin_bank_src_version%
set bitcoin_bank_src_version_name=%bitcoin_bank_src_version_name: =_%

echo Version: '%bitcoin_bank_src_version%'

for /f "tokens=1,2" %%a in ("%bitcoin_bank_src_version%") do (
    set v1=%%a
    set v2=%%b
    echo v1: %v1%
    echo v2: %v2%
)

for /f "tokens=1,2" %%a in ("%bitcoin_bank_src_version_label%") do (
    set v1_label=%%a
    set v2_label=%%b
    echo v1_label: %v1_label%
    echo v2_label: %v2_label%
)

Rem Check if version is prepended with edition name
echo %v1%| findstr /r "^[a-zA-Z]*$">nul
if %errorlevel%==0 (
    echo Version has edition name
    set bitcoin_bank_src_edition=%v1%
    set bitcoin_bank_src_version=%v2%
    set bitcoin_bank_src_edition_label=%v1_label%
    set bitcoin_bank_src_version_label=%v2_label%
) else (
    echo Standard edition
    set bitcoin_bank_src_edition=standard
    set bitcoin_bank_src_version=%v1%
    set bitcoin_bank_src_edition_label=
    set bitcoin_bank_src_version_label=%v1_label%
)

echo Edition: '%bitcoin_bank_src_edition%'
echo Version: '%bitcoin_bank_src_version%'

rem Check for valid edition names
if %bitcoin_bank_src_edition% == standard goto edition_ok
if %bitcoin_bank_src_edition% == premium goto edition_ok
echo Unknown edition name
goto error

:edition_ok

Rem check if version is a pre-release
for /f "tokens=1,2 delims=-" %%a in ("%bitcoin_bank_src_version%") do (
    set v3=%%a
    set v4=%%b
    echo v3: %v3%
    echo v4: %v4%
)

Rem Check if version is prepended with edition name
echo %v4%| findstr /r "^[a-z0-9]*$">nul
if %errorlevel%==0 (
    echo Version is pre-release
    set bitcoin_bank_src_version=%v3%
    set bitcoin_bank_src_release=%v4%
) else (
    echo Version is stable release
    set bitcoin_bank_src_version=%v3%
    set bitcoin_bank_src_release=stable
)

echo Version: '%bitcoin_bank_src_version%'
echo Release: '%bitcoin_bank_src_release%'

rem Check if version number is ok, must start with digit and only contains digits and dots
echo %bitcoin_bank_src_version%| findstr /r "^[0-9][0-9.]*$">nul
if %errorlevel%==0 (
    echo Version number OK
) else (
    echo Invalid version number
    goto error
)

Rem Check if release string is correct
echo %bitcoin_bank_src_release%| findstr /r "^[a-zA-Z0-9]*$">nul
if %errorlevel%==0 (
    echo Release string ok
) else (
    echo Release characters wrong
    goto error
)

Rem check if version is a pre-release
for /f "tokens=1 delims=1234567890" %%a in ("%bitcoin_bank_src_release%") do (
    set bitcoin_bank_src_release_type=%%a
)

echo.
echo Summary:
echo Edition: '%bitcoin_bank_src_edition%'
echo Edition label: '%bitcoin_bank_src_edition_label%'
echo Version: '%bitcoin_bank_src_version%'
echo Version label: '%bitcoin_bank_src_version_label%'
echo Release: '%bitcoin_bank_src_release%'
echo Release type: '%bitcoin_bank_src_release_type%'
echo Version name: '%bitcoin_bank_src_version_name%'

set bitcoin_bank_src_package_name=bitcoin-bank_%bitcoin_bank_src_version_name%
echo Package name: '%bitcoin_bank_src_package_name%'

set bitcoin_bank_src_zip_file_name=%bitcoin_bank_src_package_name%.zip
echo Zip file name: '%bitcoin_bank_src_zip_file_name%'

echo Tested up to: '%bitcoin_bank_src_wp_tested%'

:ok
echo Version name read OK
set bitcoin_bank_src_error_code=0
goto end

:error
echo ERROR reading version name
set bitcoin_bank_src_error_code=1
goto end

:end
