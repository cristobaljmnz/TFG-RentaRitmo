#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Feb  2 12:13:46 2024

@author: cristobaljimenez_
"""


import pandas as pd
import numpy as np
import rentPriceRegressorFunciones
from unidecode import unidecode
from calculoRentabilidad import calcular_datos_rentabilidad

def predecirSobreVenta():
    file_path = './datasetsSale/nuevo_con_interest_points.csv'
    enVenta = pd.read_csv(file_path, na_values="NaN")
    
    enVenta = rentPriceRegressorFunciones.soloGranadaCiudad(enVenta)
    
    # el nuevo conjunto train será todo el dataset de alquileres. 
    train = pd.read_csv("./datasetsRent/fusionado_con_interest_points.csv")

    #preprocesar datos y guardar algunos atributos que no sirven para el modelo pero necesitaremos:
    #---------------------------------------------------------------------------------------------------------
    # en el otro .py al separar en train y test ya se hicieron estos pasos:
    enVenta = rentPriceRegressorFunciones.dropNoFiables(enVenta)
    enVenta = enVenta.drop_duplicates()
    urls_venta = enVenta.url
    link_imagenes = enVenta.thumbnail
    # NUEVAS
    municipality = enVenta.municipality
    address = enVenta.address
    size_vivienda = enVenta['size']
    latitud = enVenta.latitude
    longitud = enVenta.longitude
    rooms = enVenta.rooms
    bathrooms = enVenta.bathrooms

    # quitamos el precio de venta de la vivienda para calcular el de alquiler
    precio_venta = enVenta.price
    enVenta = enVenta.drop(columns=['price'])

    # train_preprocesado, enVenta_preprocesado = rentPriceRegressorFunciones.preprocesar(train, enVenta)
    train_preprocesado = rentPriceRegressorFunciones.dropNoFiables(train)
    train_preprocesado = rentPriceRegressorFunciones.eliminarCaracteristicas(train_preprocesado)
    enVenta_preprocesado = rentPriceRegressorFunciones.eliminarCaracteristicas(enVenta)

    train_preprocesado = rentPriceRegressorFunciones.crearAmenities(train_preprocesado)
    enVenta_preprocesado = rentPriceRegressorFunciones.crearAmenities(enVenta_preprocesado)

    train_preprocesado = rentPriceRegressorFunciones.tratadoVariablesNumericas(train_preprocesado)
    enVenta_preprocesado = rentPriceRegressorFunciones.tratadoVariablesNumericas(enVenta_preprocesado)

    train_preprocesado = rentPriceRegressorFunciones.tratadoVariablesCategoricas(train_preprocesado)
    enVenta_preprocesado = rentPriceRegressorFunciones.tratadoVariablesCategoricas(enVenta_preprocesado)

    # AQUÍ ELIMINAR ALGUNAS INSTANCIAS DE ENVENTA, que no pueden ser explicadas con viviendas en alquiler
    # Obtener los valores únicos de la columna 'subtypology' en train_preprocesado
    subtypology_train = train_preprocesado['subTypology'].unique()
    print("HOLAAAAAAAAAAAAA")
    print(train_preprocesado['subTypology'].unique())
    print("HOLAAAAAAAAAAAAA")
    print(enVenta_preprocesado['subTypology'].unique())
    # mantener solo las filas donde 'subtypology' esté en los valores únicos de train_preprocesado
    # Crear una máscara para filtrar enVenta_preprocesado y aplicar la misma máscara al resto de características que teníamos guardadas
    mask_subtypology = enVenta_preprocesado['subTypology'].isin(subtypology_train)

    # Aplicar la máscara a enVenta_preprocesado y a las características adicionales
    enVenta_preprocesado = enVenta_preprocesado[mask_subtypology]
    precio_venta = precio_venta[mask_subtypology]
    urls_venta = urls_venta[mask_subtypology]
    link_imagenes = link_imagenes[mask_subtypology]
    municipality = municipality[mask_subtypology]
    address = address[mask_subtypology]
    size_vivienda = size_vivienda[mask_subtypology]
    latitud = latitud[mask_subtypology]
    longitud = longitud[mask_subtypology]
    rooms = rooms[mask_subtypology]
    bathrooms = bathrooms[mask_subtypology]

    train_preprocesado, enVenta_preprocesado = rentPriceRegressorFunciones.crearDummiesyOrdenar(train_preprocesado, enVenta_preprocesado)

    for column in train_preprocesado.columns:
        print(f"Posibles valores en la columna '{column}':")
        unique_values = train_preprocesado[column].value_counts()
        print(unique_values)
        print("\n" + "="*50 + "\n")  # Separador para mayor claridad

    train_preprocesado, enVenta_preprocesado = rentPriceRegressorFunciones.normalizarVariablesNumericas(train_preprocesado, enVenta_preprocesado)

    rentPriceRegressorFunciones.visualizarDatos(train_preprocesado)

    has_pool = enVenta_preprocesado.pool
    has_Lift = enVenta_preprocesado.hasLift
    newDevelopment = enVenta_preprocesado.newDevelopment
    hasParkingSpace = enVenta_preprocesado.hasParkingSpace
    forStudents = enVenta_preprocesado.forStudents
    #creamos una variable "es_casa" que nos valdrá para estimar los gastos de comunidad 
    # Calcular la columna 'es_casa'
    es_casa = (
        (enVenta_preprocesado.subTypology_terracedHouse == 1) |
        (enVenta_preprocesado.subTypology_semidetachedHouse == 1) |
        (enVenta_preprocesado.subTypology_independantHouse == 1) |
        (enVenta_preprocesado.subTypology_chalet == 1)
    ).astype(int)
    #---------------------------------------------------------------------------------------------------------




    #entrenar modelo con todos los datos de rent:
    #---------------------------------------------------------------------------------------------------------

    X_enVenta = enVenta_preprocesado
    y_train = train_preprocesado.price
    X_train = train_preprocesado.drop(columns=['price'])

    # Aplicar logaritmo
    y_train_log = np.log1p(y_train)
    print(X_enVenta.columns)
    
    #reentrenar el modelo
    best_xgb_model, best_lgb_model = rentPriceRegressorFunciones.entrenarModelo(X_train, y_train_log)
    models = [best_xgb_model, best_lgb_model]
    pred_alquiler_log = rentPriceRegressorFunciones.combinarPredicciones(models, X_enVenta)
    pred_alquiler = np.expm1(pred_alquiler_log)
    mask = pred_alquiler < 1300
    
    #---------------------------------------------------------------------------------------------------------




    #aplicar máscara a todos los datos(acepto predicciones de <1300€ de alquiler)
    #---------------------------------------------------------------------------------------------------------

    pred_filtradas = pred_alquiler[mask]
    urls_venta_filtradas = urls_venta[mask]
    precio_venta_filtradas = precio_venta[mask]
    link_imagenes_filtradas = link_imagenes[mask]
    has_Lift_filtradas = has_Lift[mask]
    size_vivienda_filtradas = size_vivienda[mask]
    latitud_filtradas = latitud[mask]
    longitud_filtradas = longitud[mask]
    has_pool_filtradas = has_pool[mask]
    newDevelopment_filtradas = newDevelopment[mask]
    municipality_filtradas = municipality[mask]
    address_filtradas = address[mask]
    rooms_filtradas = rooms[mask]
    bathrooms_filtradas = bathrooms[mask]
    hasParkingSpace_filtradas = hasParkingSpace[mask]
    forStudents_filtradas = forStudents[mask]
    es_casa_filtradas= es_casa[mask]
    #---------------------------------------------------------------------------------------------------------


    #guardar lo calculado en csvs
    #---------------------------------------------------------------------------------------------------------
    # Crear una nueva columna "id" con valores incrementales
    id_vivienda = range(1, len(pred_filtradas) + 1)
    
    #modificar cuando suba a pagina web
    links = ["http://www.rentaritmo.online/detalle_vivienda.php?id=" + str(id) for id in id_vivienda]
    # quitar acentos y otros caracteres no ascii para poder importar en la base de datos
    municipality_filtradas_ascii = [unidecode(texto) for texto in municipality_filtradas]
    address_filtradas_ascii = [unidecode(texto) for texto in address_filtradas]


    # Guardar las predicciones en un archivo CSV
    salida_predicciones = pd.DataFrame({
        'url': urls_venta_filtradas, 
        'Precio_venta': precio_venta_filtradas, 
        'Prediccion_Alquiler': pred_filtradas
    })
    salida_predicciones.to_csv("./resultado/prediccionSale.csv", index=False)

    # Guardar datos de las viviendas necesarios para calcular rentabilidad en un archivo CSV
    salida_predicciones = pd.DataFrame({
        'id_vivienda': id_vivienda,
        'url': urls_venta_filtradas,
        'Link_Imagenes': link_imagenes_filtradas,
        'has_Lift': has_Lift_filtradas,
        'size_vivienda': size_vivienda_filtradas,
        'latitud': latitud_filtradas,
        'longitud': longitud_filtradas,
        'has_pool': has_pool_filtradas,
        'Precio_venta': precio_venta_filtradas,
        'Prediccion_Alquiler': pred_filtradas,
        'New_Development': newDevelopment_filtradas,
        'es_casa': es_casa_filtradas
    })
    salida_predicciones.to_csv("./resultado/datosParaCalcularRentabilidad.csv", index=False)

    
    salida_viviendas = pd.DataFrame({
        'id_vivienda': id_vivienda,
        'link': links,
        'url': urls_venta_filtradas,
        'Link_Imagenes': link_imagenes_filtradas,
        'has_Lift': has_Lift_filtradas,
        'size_vivienda': size_vivienda_filtradas,
        'latitude': latitud_filtradas,
        'longitude': longitud_filtradas,
        'has_pool': has_pool_filtradas,
        'Precio_venta': precio_venta_filtradas,
        'Prediccion_Alquiler': pred_filtradas,
        'New_Development': newDevelopment_filtradas,
        'for_students': forStudents_filtradas,
        'has_parking': hasParkingSpace_filtradas,
        'rooms': rooms_filtradas,
        'bathrooms': bathrooms_filtradas,
        'municipality': municipality_filtradas_ascii,
        'address': address_filtradas_ascii, 
        'es_casa': es_casa_filtradas
    })
    salida_viviendas.to_csv("./resultado/datosViviendas.csv", index=False, encoding='ascii')
    #---------------------------------------------------------------------------------------------------------

    #añadir un primer cálculo de rentabilidad para poder filtrar y ordenar en la página web por estos atributos
    #---------------------------------------------------------------------------------------------------------
    calcular_datos_rentabilidad()
    #---------------------------------------------------------------------------------------------------------

   
if __name__ == "__main__":
    predecirSobreVenta()
   
    
   
    