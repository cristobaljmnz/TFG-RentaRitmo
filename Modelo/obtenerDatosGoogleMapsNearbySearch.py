#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Sun Apr 14 17:18:01 2024

@author: cristobaljimenez_
"""


#  CAMBIAR NOMBRES CSV Y "TIPO". HAY QUE FILTRAR MANUALMENTE ALGUNOS DATOS DESCARGADOS DE LA API DE GOOGLE MAPS
#guía:https://developers.google.com/maps/documentation/places/web-service/search-nearby?hl=es-419

# API KEY: AIzaSyDE_BxqhETg16olI4yK20Sr4qokhBdnUag

# manual places API:https://github.com/googlemaps/google-maps-services-python/blob/master/googlemaps/places.py

# Cómo agregar la clave de API a tu solicitud

# Debes incluir una clave de API con cada solicitud a la API de Places. En el siguiente ejemplo, reemplaza YOUR_API_KEY por tu clave de API.

# https://places.googleapis.com/v1/places/ChIJj61dQgK6j4AR4GeTYWZsKWw?fields=id,displayName&key=YOUR_API_KEY
# Se requiere HTTPS para las solicitudes que usan una clave de API.


import requests as rq
import json
import pandas as pd
import time
import os

def obtener_datos_google_maps():
    base_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?'
    center_loc = '37.179580%2C-3.599720' # %2C == ','  se usa en codificación URL para que no haya problemas con la coma
    distance ='6000'
    api_key = 'AIzaSyDE_BxqhETg16olI4yK20Sr4qokhBdnUag'
    idioma = 'es'
    sort = 'prominence' #rankby importance of site
    puntos_interes = [
        "supermarket",
        "convenience_store",
        "pharmacy",
        "bus_station",
        "train_station",
        "restaurant",
        "cafe",
        "bar",
        "park",
        "gym",
        "primary_school",
        "secondary_school",
        "doctor"
    ]
    def search_api(url):
        content = rq.post(url)
        result = json.loads(content.text)
        return result
    
    # all_concat_dfs = []

    for tipo in puntos_interes:
        url1 = (base_url + 
                'location=' + center_loc +
                '&radius=' + distance +
                '&type=' + tipo +
                '&language=' + idioma +
                '&rankby=' + sort +
                '&key=' + api_key)

        results = search_api(url1)

        if 'next_page_token' in results:
            next_page_token = results['next_page_token']
            print(f'next_page_token for {tipo}=', next_page_token)
        else:
            next_page_token = None

        # Crear directorio si no existe
        os.makedirs("./datosMaps", exist_ok=True)

        df = pd.DataFrame.from_dict(results['results'])
        df.to_csv(f"./datosMaps/{tipo}1.csv", index=False)  # Save page 1

        concatB = pd.read_csv(f"./datosMaps/{tipo}1.csv")

        # Hay un límite de 3 páginas con 20 resultados cada una
        #y sólo se puede hacer la búsqueda una a una. Por lo tanto hay que ver si en la 
        #primera búsqueda hay una siguiente página (next_page_token no es null) 
        #y en la segunda igual
        if next_page_token is not None:
            new_url = (base_url + 
                       'pagetoken=' + next_page_token +
                       '&location=' + center_loc +
                       '&radius=' + distance +
                       '&key=' + api_key)
            time.sleep(10)

            results = search_api(new_url)

            if 'next_page_token' in results:
                next_page_token2 = results['next_page_token']
                print(f'next_page_token2 for {tipo}=', next_page_token2)
            else:
                next_page_token2 = None
                print(f'null2 for {tipo}\n')

            df = pd.DataFrame.from_dict(results['results'])
            df.to_csv(f"./datosMaps/{tipo}2.csv", index=False)  # Save page 2

            concatB = pd.concat([concatB, df])

            if next_page_token2 is not None:
                new_url = (base_url + 
                           'pagetoken=' + next_page_token2 +
                           '&location=' + center_loc +
                           '&radius=' + distance +
                           '&key=' + api_key)

                time.sleep(10)

                results = search_api(new_url)

                if 'next_page_token' in results:
                    next_page_token3 = results['next_page_token']
                    print(f'next_page_token3 for {tipo}=', next_page_token3)
                else:
                    next_page_token3 = None
                    print(f'null3 for {tipo}\n')

                df = pd.DataFrame.from_dict(results['results'])
                df.to_csv(f"./datosMaps/{tipo}3.csv", index=False)  # Save page 3
                time.sleep(2)
                concatB = pd.concat([concatB, df])

        concatB.to_csv(f'./datosMaps/datos_{tipo}.csv', index=False)
        print(f'Datos guardados en ./datosMaps/datos_{tipo}.csv')
    #     all_concat_dfs.append(concatB)

    # all_data = pd.concat(all_concat_dfs, ignore_index=True)
    # all_data.to_csv('./datosMaps/datos_todos_puntos_de_interes.csv', index=False)
    # print('Datos combinados guardados en ./datosMaps/datos_todos_puntos_de_interes.csv')

    # return './datosMaps/datos_todos_puntos_de_interes.csv'

