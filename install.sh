#!/bin/sh

if ! [ -x "$(command -v pg_ctl)" ]; then
    ispgsql=0
else
    ispgsql=1
fi

while :
do

    clear
    echo "Lightframe console"
    echo "------------------"
    echo "Opciones:"
    echo "1: Descomprimir plugins"
    echo "2: Descomprimir base de datos"
    if [[ $ispgsql == 1 ]]; then
        if ! [[ $(pg_ctl status -D database | grep 'PID:') > /dev/null ]]; then
            echo "3: Iniciar base de datos"
        else
            echo "3: Detener base de datos"
        fi
    else
        echo "(No se encuentra pg_ctl en PATH)"
    fi
    echo "4: Abrir VS Code en este directorio"
    echo "5: Iniciar PHP CLI"
    echo "------------------"
    read -p "Ingrese la opción y pulse enter: " option
    echo "------------------"

    if [ $option == 1 ]; then
        echo Eliminando carpetas en directorio public...
        rm -rf "public/app-assets/";
        rm -rf "public/bower_components/";
        rm -rf "public/dist/";
        rm -rf "public/fonts/";
        rm -rf "public/plugins/";
        rm -rf "public/webfonts/";

        #Descomprimir y sobreescribir carpetas en public
        echo Descomprimiendo carpetas en directorio public...
        unzip -qq -o public/public.zip -d public
    fi

    if [ $option == 2 ]; then
        #Ver si existe carpeta de base de datos
        if [ -d "database/" ]; then
            echo Base de datos existente. Eliminando...
            rm -rf "database/";
        fi
        echo Descomprimiendo base de datos...
        #Descomprimir base de datos
        unzip -qq database.zip
    fi

    if [[ $option == 3 && $ispgsql == 1 ]]; then
        if ! [[ $(pg_ctl status -D database | grep 'PID:') > /dev/null ]]; then
            pg_ctl start -D database
        else
            pg_ctl stop -D database
        fi
        
    fi

    if [ $option == 4 ]; then
        code .
    fi

    if [ $option == 5 ]; then
        php -q -S 127.0.0.1:80 -t ./public
    fi

    echo "------------------"
    read -p "Presione enter para continuar..."

done