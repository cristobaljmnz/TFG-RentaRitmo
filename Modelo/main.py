#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri May 24 23:44:00 2024

@author: cristobaljimenez_
"""

import os
import pandas as pd
from obtenerdatosAPI import fetch_idealista_data
from mapsAPI.obtenerDatosGoogleMapsNearbySearch import obtener_datos_google_maps
from fusionartodosrent import fusionar_csv
from calcularDistanciaAPntInteres import calcularDistanciaPuntosInteres
from separarTrainTest import separar_train_test
from rentPriceRegressorMain import ejecutar_prediccion_rentabilidad
from rentPriceRegressorFunciones import preprocesartodo, soloGranadaCiudad,visualizarDatos,dropOutliers,elbow_method_kmeans
from regresorSobreVenta import predecirSobreVenta

def main():
    get_idealista_data = input("¿Quieres obtener datos de la API de Idealista? (sí/no): ").strip().lower()
    if get_idealista_data not in ['sí', 'si', 'no']:
        print("Opción no válida. Por favor, elige 'sí' o 'no'.")
        return
    
    if get_idealista_data in ['sí', 'si']:
        operation = input("¿Quieres obtener datos de venta, de alquiler o ambos? (sale/rent/ambos): ").strip().lower()
        if operation not in ['sale', 'rent', 'ambos']:
            print("Opción no válida. Por favor, elige 'sale', 'rent' o 'ambos'.")
            return
        
        operations = ['rent', 'sale'] if operation == 'ambos' else [operation]

        for op in operations:
            # Directorio base
            base_dir = f"./datasets{op.capitalize()}/buscarNuevos/"
            os.makedirs(base_dir, exist_ok=True)
            
            # Llamada a la función para obtener los datos
            fetch_idealista_data(op, base_dir)
            print(f"Datos de {op} obtenidos y guardados en {base_dir}")
        
        #si hemos cogido nuevos datos de rent los fusionamos con los anteriores
        if operation == 'rent' or operation == 'ambos':
            fusionar_csv("./datasetsRent/fusionado_con_interest_points.csv", "./datasetsRent/buscarNuevos/datos_idealistaNuevos.csv")
        
        #si hemos cogido nuevos datos de sale actualizamos el csv de "nuevos_con_interes_points" por el nuevo dataset de viviendas a la venta
        #posteriormente calcularemos los interest_points
        if operation == 'sale' or operation == 'ambos':
            path_nuevo = "./datasetsSale/nuevo_con_interest_points.csv"
            path_datos_idealista_nuevos = "./datasetsSale/buscarNuevos/datos_idealistaNuevos.csv"
            os.replace(path_datos_idealista_nuevos, path_nuevo)
            
            
    else:
        print("No se obtendrán datos de la API de Idealista.")
     
        
    get_google_data = input("¿Quieres obtener datos de Google Maps o usar los que ya están almacenados? (sí/no): ").strip().lower()
    if get_google_data not in ['sí', 'si', 'no']:
        print("Opción no válida. Por favor, elige 'sí' o 'no'.")
        return

    if get_google_data in ['sí', 'si']:
        # Llamada a la función para obtener los datos de Google Maps
        obtener_datos_google_maps()
        #aquí hay un paso hecho a mano, y es que las universidades y hospitales de google maps han sido 
        #revisadas y filtradas para dejar sólo las instancias que son correctas 
        #(pues había otros establecimientos autonombrados como universidad u hospital)
        #por lo tanto estos datos no se actualizarán con nuevas búsquedas
        # hecho en ./mapsAPI/datosMaps/eliminarerroneos.py
        
    #si hemos elegido coger nuevos datos de google maps o de idealista, calcularemos la columna 
    #"interest_points" para todos los datos que tenemos
    
    if get_idealista_data in ['sí', 'si'] or get_google_data in ['sí', 'si']:
        if operation == 'rent' or operation == 'ambos' or get_google_data in ['sí', 'si']:
            ubicacion_archivo = "./datasetsRent/fusionado_con_interest_points.csv"
            calcularDistanciaPuntosInteres(ubicacion_archivo)
            
            # Cargar datos y preprocesar antes de separar en train y test
            data = pd.read_csv(ubicacion_archivo, na_values="NaN")
            data = soloGranadaCiudad(data)
            data = dropOutliers(data)
            
            
            #actualizamos train test para entrenar modelo
            file_path = './datasetsRent/fusionado_con_interest_points.csv'
            train_path = './datasetsRent/train_dataset_interest_points.csv'  
            test_path = './datasetsRent/test_dataset_interest_points.csv' 

            separar_train_test(file_path, train_path, test_path)
            
            
        if operation == 'sale' or operation == 'ambos' or get_google_data in ['sí', 'si']:
            ubicacion_archivo = "./datasetsSale/nuevo_con_interest_points.csv"
            calcularDistanciaPuntosInteres(ubicacion_archivo)
    
    #modelo para rent
    
    entrenar_modelo = True  # Cambiar a False si deseas usar un modelo preentrenado
    ejecutar_prediccion_rentabilidad(entrenar_modelo)
    
    #modelo sobre venta y calcular rentabilidad:
    predecirSobreVenta()
    
    
    
    

if __name__ == "__main__":
    main()
