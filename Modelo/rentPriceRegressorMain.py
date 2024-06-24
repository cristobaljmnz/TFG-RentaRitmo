#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Apr 24 17:23:01 2024

@author: cristobaljimenez_
"""

import pandas as pd
import numpy as np
import rentPriceRegressorFunciones
import joblib

def ejecutar_prediccion_rentabilidad(entrenar_modelo):
    # LEER DATOS 
    train = pd.read_csv("./datasetsRent/train_dataset_interest_points.csv", na_values="NaN")
    test = pd.read_csv("./datasetsRent/test_dataset_interest_points.csv", na_values="NaN")

    test = rentPriceRegressorFunciones.dropNoFiables(test)

    y_test=test.price
    test = test.drop(columns=['price'])
    test_urls= test.url

    
    
    train_preprocesado, test_preprocesado = rentPriceRegressorFunciones.preprocesar(train, test)

    rentPriceRegressorFunciones.visualizarDatos(train_preprocesado)

    y_train = train_preprocesado.price
    X_train = train_preprocesado.drop(columns=['price'])
    X_test = test_preprocesado

    # Aplicar logaritmo al precio en los conjuntos de entrenamiento y prueba
    y_train_log = np.log1p(y_train)
    y_test_log = np.log1p(y_test)

    print("\nInstancias en train:",X_train.shape[0])
    print("Instancias en test:",X_test.shape[0])

    
    # Entrenar modelos y combinar predicciones
    if entrenar_modelo:
        best_xgb_model, best_lgb_model = rentPriceRegressorFunciones.entrenarModelo(X_train, y_train_log)
    else:
        best_xgb_model = joblib.load('modelo_xgb_entrenado.pkl')
        best_lgb_model = joblib.load('modelo_lgb_entrenado.pkl')
        # best_knn_model = joblib.load('modelo_knn_entrenado.pkl')

    models = [best_xgb_model, best_lgb_model]#, best_knn_model]
    pred_log = rentPriceRegressorFunciones.combinarPredicciones(models, X_test)
    pred = np.expm1(pred_log)

    pred = np.expm1(pred_log)

    # Crear una máscara booleana para identificar las predicciones menores a 1300
    # NO ACEPTAREMOS PREDICCIONES MAYORES A 1300, SON MÁS PROPENSAS A FALLAR
    # SE DIRÁ QUE LA PREDICCIÓN DE PRECIO DE ALQUILER ES >1300
    #EL MODELO SE VUELVE BASTANTE PESIMISTA CON LOS PRECIOS (TASA A LA BAJA), CUANDO EL PRECIO ES ALTO
    mask = pred < 1300
    # Filtrar las predicciones utilizando la máscara booleana
    pred_filtradas = pred[mask]
    print("Cantidad de predicciones ya filtradas:", len(pred_filtradas))

    # Filtrar las URLs de test utilizando la máscara booleana
    test_urls_filtradas = test_urls[mask]

    # Filtrar los valores reales de test utilizando la máscara booleana
    y_test_filtradas = y_test[mask]
    rentPriceRegressorFunciones.visualizarPredicciones(y_test_filtradas, pred_filtradas)
    rentPriceRegressorFunciones.guardarResultados(test_urls_filtradas, pred_filtradas)


def main():
    entrenar_modelo = True  
    ejecutar_prediccion_rentabilidad(entrenar_modelo)

if __name__ == "__main__":
    main()
