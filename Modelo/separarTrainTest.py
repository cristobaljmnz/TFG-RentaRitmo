#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Feb  2 12:13:46 2024

@author: cristobaljimenez_
"""

import pandas as pd
from sklearn.model_selection import train_test_split

def separar_train_test(file_path, train_path, test_path):
    # Carga el conjunto de datos desde el archivo CSV
    df = pd.read_csv(file_path, na_values="NaN")

    # print(df.shape)
    # ELIMINAR DUPLICADOS (después de borrar Unnamed: 0)
    if 'Unnamed: 0' in df.columns:
        df = df.drop(columns=['Unnamed: 0']) #priceInfo viene con la API en los datos de ABRIL, en los de antes no, y no es info relevante
    # df_no_duplicates = df.drop_duplicates()
    # print(df_no_duplicates.shape)
    # la columna objetivo se llama 'price'
    X = df.drop('price', axis=1)  # Características (todas las columnas excepto la columna objetivo)
    y = df['price']  # Columna objetivo

    # dividir los datos al 70-30, ya que no tenemos un dataset demasiado grande y así podemos evaluar el modelo
    #y que sea más fiable 
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=42)

    # Guardar los conjuntos de entrenamiento y prueba en archivos CSV
    # Incluir de primeras la columna objetivo en ambos conjuntos de datos, para tenerla
    train_data = pd.concat([X_train, y_train], axis=1)
    test_data = pd.concat([X_test, y_test], axis=1)

    train_data.to_csv(train_path, index=False, header=True)
    test_data.to_csv(test_path, index=False, header=True)