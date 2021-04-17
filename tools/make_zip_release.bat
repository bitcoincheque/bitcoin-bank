@echo off

echo RML Zip Packager

call read_version.bat

if not %bitcoin_bank_src_error_code%==0 goto error

echo From version name reader:
echo Edition: '%bitcoin_bank_src_edition%'
echo Edition label: '%bitcoin_bank_src_edition_label%'
echo Version: '%bitcoin_bank_src_version%'
echo Version label: '%bitcoin_bank_src_version_label%'
echo Release: '%bitcoin_bank_src_release%'
echo Release type: '%bitcoin_bank_src_release_type%'
echo Version name: '%bitcoin_bank_src_version_name%'
echo Package name: '%bitcoin_bank_src_package_name%'
echo Zip file name: '%bitcoin_bank_src_zip_file_name%'

echo.
set bitcoin_bank_src_output_path=C:\sandbox\htdocs\bitcoin_bank_src_output\
echo Output directory: '%bitcoin_bank_src_output_path%'

set bitcoin_bank_src_zip_file_path=%bitcoin_bank_src_output_path%%bitcoin_bank_src_zip_file_name%
echo Zip file path: '%bitcoin_bank_src_zip_file_path%'

set bitcoin_bank_src_zip_file_list=%bitcoin_bank_src_output_path%%bitcoin_bank_src_package_name%_file_list.txt
echo Zip file list: '%bitcoin_bank_src_zip_file_list%'

set zip_app="C:\Program Files\7-Zip\7z.exe"
echo Zip app: '%zip_app%'

set current_dir="%cd%"

cd ..\..
del %bitcoin_bank_src_zip_file_path%
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\asset\css
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\asset\img
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\asset\js
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\asset\languages
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\controllers
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\data_types
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\include
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\models
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\pages
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\src\views
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\wppluginframework\wp-plugin-framework\css
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\wppluginframework\wp-plugin-framework\js
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\wppluginframework\wp-plugin-framework\src
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\wppluginframework\wp-plugin-framework\LICENSE
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\wppluginframework\wp-plugin-framework\README.md
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\composer
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\vendor\autoload.php
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\license.txt
%zip_app% a %bitcoin_bank_src_zip_file_path% bitcoin-bank\bitcoin-bank.php

%zip_app% l %bitcoin_bank_src_zip_file_path% >> %bitcoin_bank_src_zip_file_list%

cd "%current_dir%"

:ok
echo Zip package file: '%bitcoin_bank_src_zip_file_path%'
echo Zip file list: '%bitcoin_bank_src_zip_file_list%'
echo OK
set bitcoin_bank_src_error_code=0
goto end

:error
echo ERROR
set bitcoin_bank_src_error_code=1
goto end

:end
