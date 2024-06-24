#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Tue Apr 23 23:14:44 2024

@author: cristobaljimenez_
"""

#CALCULAR CARACTERÍSTICA "interest_points"

import pandas as pd
from math import radians, sin, cos, sqrt, atan2
import json
import re


# Tengo 15 csv que contienen la latitud y longitud de algunos puntos de interés. Un csv de hospitales, otro de restaurantes, otro de parques... 
#Quiero añadirlos a mi modelo de predicción del precio de alquiler, en una variable que calcule para cada vivienda (de la que tengo latitud y longitud
# un valor que será más alto mientras más puntos de interés tenga alrededor y más bajo mientras menos tenga. Para ello primero tengo que cargar todas 
#las bases de datos (que están ordenadas por orden de relevancia según Google Maps), y darle un peso de importancia a cada tipo de punto de interés.

# Estos son los datos que tengo:
# supermarket + convenience_store
# Pharmacy 
# Bus station  +Train station
# Restaurant + cafe + bar
# Park
# Gym
# Primary school + Secondary school +University
# Hospital +Doctor (centro de salud)

# Además, hay que importar el csv "./TODOS/fusionado.csv". Aquí estarán todas las viviendas, y tendrán una característica
#  llamada "latitude" y otra que será "longitude". Tengo que la distancia de cada vivienda con los puntos de interés para calcular el valor de la 
#  nueva característica de fusionado.csv que será "interest_points". Los csv de datos de puntos de interés tendrán la columna "geometry" que contendrá este tipo de dato 
#  {'location': {'lat': 37.18003199999999, 'lng': -3.602782}, 'viewport': {'northeast': {'lat': 37.1813954302915, 'lng': -3.601469369708498}, 'southwest': {'lat': 37.1786974697085, 'lng': -3.604167330291502}}}. 
#  De aquí tienes que extraer de 'location' los valores lat y lng, que serán la latitud y longitud en la que están los puntos de interés


# Diccionario para almacenar los DataFrames de cada punto de interés
datos_puntos_interes = {}

# Definir pesos de importancia para cada tipo de punto de interés
pesos_importancia = {
    "supermarket": 0.2,  # Muy poco importante, la gente suele ir en coche
    "convenience_store": 0.2,  # Muy poco importante
    "pharmacy": 0.4,  # Relativamente cerca y útil
    "bus_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
    "train_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
    "restaurant": 0.7,  # Importante para la calidad de vida
    "cafe": 0.7,  # Importante para la calidad de vida
    "bar":0.7,  # Importante para la calidad de vida
    "park": 0.8,  # Muy importante para la calidad de vida
    "gym": 0.5,  # Relativamente importante
    "primary_school": 0.8,  # Importante para familias con niños
    "secondary_school": 0.8,  # Importante para familias con niños
    "university_fixed": 0.8,  # Importante para estudiantes
    "hospital_fixed": 0.5,  # Importante para emergencias médicas
    "doctor": 0.4  # Importante para consultas médicas regulares
}


# pesos_importancia = {
#     "supermarket": 0.3,  # Muy poco importante, la gente suele ir en coche
#     "convenience_store": 0.2,  # Muy poco importante, la gente suele ir en coche
#     "pharmacy": 0.4,  # Relativamente cerca y útil
#     "bus_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "train_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "restaurant": 0.7,  # Importante para la calidad de vida
#     "cafe": 0.7,  # Importante para la calidad de vida
#     "park": 0.8,  # Importante para la calidad de vida
#     "gym": 0.5,  # Relativamente importante
#     "primary_school": 0.8,  # Importante para familias con niños
#     "secondary_school": 0.8,  # Importante para familias con niños
#     "university": 0.8,  # Importante para estudiantes
#     "hospital": 0.5,  # Importante para emergencias médicas
#     "doctor": 0.3  # Importante para consultas médicas regulares
# }

# pesos_importancia = {  # PEOR QUE EL DE ARRIBA
#     "supermarket": 0.3,  # Muy poco importante, la gente suele ir en coche
#     "convenience_store": 0.2,  # Muy poco importante, la gente suele ir en coche
#     "pharmacy": 0.4,  # Relativamente cerca y útil
#     "bus_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "train_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "restaurant": 0.7,  # Importante para la calidad de vida
#     "cafe": 0.6,  # Importante para la calidad de vida
#     "park": 0.8,  # Importante para la calidad de vida
#     "gym": 0.5,  # Relativamente importante
#     "primary_school": 0.8,  # Importante para familias con niños
#     "secondary_school": 0.8,  # Importante para familias con niños
#     "university": 0.8,  # Importante para estudiantes
#     "hospital": 0.5,  # Importante para emergencias médicas
#     "doctor": 0.3  # Importante para consultas médicas regulares
# }

# pesos_importancia = { # PEOR QUE EL DE ARRIBA
#     "supermarket": 0.2,  # Muy poco importante, la gente suele ir en coche
#     "convenience_store": 0.2,  # Muy poco importante, la gente suele ir en coche
#     "pharmacy": 0.4,  # Relativamente cerca y útil
#     "bus_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "train_station": 0.1,  # Negativo, suele ser peligroso y la gente prefiere evitarlo
#     "restaurant": 0.7,  # Importante para la calidad de vida
#     "cafe": 0.7,  # Importante para la calidad de vida
#     "park": 1,  # Importante para la calidad de vida
#     "gym": 0.5,  # Relativamente importante
#     "primary_school": 0.8,  # Importante para familias con niños
#     "secondary_school": 0.8,  # Importante para familias con niños
#     "university": 0.8,  # Importante para estudiantes
#     "hospital": 0.6,  # Importante para emergencias médicas
#     "doctor": 0.5  # Importante para consultas médicas regulares
# }

def calcularDistanciaPuntosInteres(ubicacion_archivo):
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
        "university_fixed",
        "hospital_fixed",
        "doctor"
    ]

    # Cargar los datos de cada punto de interés
    for punto_interes in puntos_interes:
        nombre_archivo = f"./mapsAPI/datosMaps/datos{punto_interes}.csv"
        datos_puntos_interes[punto_interes] = pd.read_csv(nombre_archivo)

    # Cargar el conjunto de datos fusionado que contiene las viviendas
    fusionado = pd.read_csv(ubicacion_archivo)

    # función para reemplazar las comillas simples por comillas dobles, para poder acceder a los valores en formato json de la columna "geometry"
    def convertir_a_json_valido(valor):
        valor_corregido = re.sub(r"'", '"', valor)
        return valor_corregido

    
    # fórmula para calcular distancia en metros entre dos latitudes y longitudes
    def distancia_en_metros(lat1, lon1, lat2, lon2):
        radio_tierra = 6371000
        lat1_rad, lon1_rad = radians(lat1), radians(lon1)
        lat2_rad, lon2_rad = radians(lat2), radians(lon2)
        dlat = lat2_rad - lat1_rad
        dlon = lon2_rad - lon1_rad
        a = sin(dlat / 2) ** 2 + cos(lat1_rad) * cos(lat2_rad) * sin(dlon / 2) ** 2
        c = 2 * atan2(sqrt(a), sqrt(1 - a))
        distancia = radio_tierra * c
        return distancia

    #ASÍ SE OBTIENEN LOS DATOS LAT Y LNG
    # print(json.loads(convertir_a_json_valido(datos_puntos_interes['supermarket']['geometry'].iloc[0]))['location']['lat'])

    # Calcular la métrica de densidad de puntos de interés para cada vivienda
    def calcular_metrica_densidad(vivienda_lat, vivienda_lon):
        metrica = 0
        #para cada vivienda, calculamos la distancia con TODOS los puntos de interés
        for punto_interes, datos in datos_puntos_interes.items():
            for index, row in datos.iterrows():
                geometry_valido = convertir_a_json_valido(row['geometry'])
                lat = json.loads(geometry_valido)['location']['lat']
                lon = json.loads(geometry_valido)['location']['lng']
                distancia = distancia_en_metros(vivienda_lat, vivienda_lon, lat, lon)
                # Verificar si la distancia es cero antes de realizar la operación de división
                #si es 0 asignamos un valor muy alto al  1/distancia
                # Verificar si la distancia es cero antes de realizar la operación de división
                
                if distancia != 0:
                    peso = pesos_importancia.get(punto_interes, 1)
                    metrica += 1 / distancia * peso
                elif distancia == 0:
                    peso = pesos_importancia.get(punto_interes, 1)
                    metrica += 1 / 10 * peso
        return metrica

    # Suponiendo que tienes un DataFrame llamado 'fusionado' que contiene las viviendas con las columnas 'latitude' y 'longitude'
    fusionado['interest_points'] = fusionado.apply(lambda row: calcular_metrica_densidad(row['latitude'], row['longitude']), axis=1)

    # Guardar el resultado en un nuevo archivo CSV
    fusionado.to_csv(ubicacion_archivo, index=False)