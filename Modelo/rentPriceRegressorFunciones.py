#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Feb  2 12:13:46 2024

@author: cristobaljimenez_
"""

import pandas as pd
import numpy as np
from xgboost import XGBRegressor
import xgboost as xgb
from sklearn.model_selection import KFold, cross_val_score,GridSearchCV, LeaveOneOut,learning_curve
import seaborn as sns
import matplotlib.pyplot as plt
from sklearn.metrics import mean_squared_error, mean_absolute_error
from scipy.stats import skew
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import r2_score
import joblib
from scipy.stats import boxcox
import re
import lightgbm as lgb
import logging
from scipy.cluster.hierarchy import dendrogram, linkage
from sklearn.cluster import AgglomerativeClustering
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import silhouette_samples, silhouette_score
from sklearn.cluster import KMeans
from sklearn.metrics import pairwise_distances_argmin_min
from sklearn.neighbors import KNeighborsRegressor
import warnings
from sklearn.linear_model import Lasso
from lightgbm import LGBMRegressor
import time

def eliminarCaracteristicas(df):
    # Eliminamos características inútiles unnamed: 0 es el id dentro de la página de 50 inmuebles
    df = df.drop(columns=['propertyCode', 'thumbnail', 'externalReference', 'showAddress','country', 'url', 'highlight', 'operation', 'newDevelopmentFinished'])
    if 'labels' in df.columns:
        df = df.drop(columns=['labels'])
    if 'priceInfo' in df.columns:
        df = df.drop(columns=['priceInfo'])
    if 'Unnamed: 0' in df.columns:
        df = df.drop(columns=['Unnamed: 0'])
    if 'municipality' in df.columns:
        df = df.drop(columns=['municipality'])
    # Eliminamos características que podrían servir pero contienen muy pocos valores diferentes (casi ninguno, totalmente desbalanceadas)
    df = df.drop(columns=['hasStaging', 'topPlus','province', 'topNewDevelopment','status'])
    # Eliminamos características a las que no sé sacarles partido todavía
    df = df.drop(columns=['suggestedTexts', 'address','neighborhood', 'distance','district']) #distance sólo marca la distancia desde el punto céntrico desde el que saqué los 
    #eliminar características del anuncio
    df = df.drop(columns=['numPhotos','hasVideo', 'hasPlan', 'has360','has3DTour'])
    
    return df

def dropNoFiables(df):
    
    #crear una característica que determine la calidad del anuncio PARA ELIMINAR AQUELLOS QUE NO SEAN FIABLES
    #teniendo en cuenta las características: 'numPhotos',  'hasVideo', 'hasPlan', 'has360', description y si el anuncio está completo
    
    #primero calcular la puntuación para cada característica
    df['photos_score'] = df['numPhotos'].apply(lambda x: -13 if x == 0 else (-10 if 0 < x <= 5 else(1 if 5 < x <= 10 else (5 if 10 < x <= 20 else (10 if 20< x <= 30 else 15)))))
    df['video_score'] = df['hasVideo'].apply(lambda x: 0 if x else 10)
    df['plan_score'] = df['hasPlan'].apply(lambda x: 0 if x else 5)
    df['360_score'] = df['has360'].apply(lambda x: 0 if x else 5)
    df['3D_score'] = df['has3DTour'].apply(lambda x: 0 if x else 5)
    df['completeness_score'] = df.apply(lambda row: 0 if row.isnull().any() else 5, axis=1)
    df['description_score'] = df['description'].str.len().apply(lambda x: -5 if x < 10 else (0 if -5 <= x <= 50 else 5))
    # ahora sumar las puntuaciones de todas las características
    df['quality_score_anuncio'] = df['photos_score'] + df['video_score'] + df['plan_score'] + df['360_score'] + df['completeness_score'] + df['description_score'] + df['3D_score']

    #quitar anuncios no fiables
    df = df.drop(df[(df['quality_score_anuncio']<=10)].index)
    #eliminar todas las características del anuncio
    df = df.drop(columns=['photos_score', 'video_score', 'plan_score', '360_score', 'completeness_score','description_score','3D_score', 'quality_score_anuncio'])
    
    return df

# la expresión regular r'\bno\b\s+\w+\s+\w+\s+\w+\s+estudiantes\b' se asegura de que "estudiantes" no esté precedida por la palabra "no" en las cuatro palabras anteriores.
def for_students_check(description):
    match = re.search(r'\bestudiantes\b', description)
    if match:
        # Verificar si las cuatro palabras anteriores no contienen "no"
        if not re.search(r'\bno\b\s+\w+\s+\w+\s+\w+\s+estudiantes\b', description[:match.start()]):
            return 1
    return 0

def crearAmenities(df):
    # Creamos nuevas características a partir de información contenida en campos como 'description'
    df['description'] = df['description'].astype(str)
    df["pool"] = df['description'].str.contains('piscina')
    df["hasFurniture"] = df['description'].str.contains(r'\bamueblado\b|\bamueblada\b', case=False, regex=True)
    df["forStudents"] = df['description'].apply(for_students_check)
    
    df = df.drop(columns=['description'])
    return df

def tratadoVariablesNumericas(df):
    # Normalizamos características booleanas a 0 o 1 mientras mantenemos los NaN como NaN
    boolean_columns = ['hasLift', 'exterior', 'pool', 'hasFurniture', 'forStudents','newDevelopment']
    for column in boolean_columns:
        df[column] = df[column].map({True: 1, False: 0, None: None})
        
    #floor empieza siendo tipo string, al tener los bajos, semisótanos y entreplantas como string, sótano es st, lo pondré como -1
    df['floor'] = df['floor'].replace({'bj': '0', 'ss':'-1', 'en':'0', 'st':'-1'})
    
    
    # Lo convertimos a float, no sin antes imputar sus valores perdidos (sólo los pisos tienen valor floor)
    #lo imputo por mediana, ya que una casa suele ser mejor que un bajo, no voy a poner planta 0, y tampoco tengo la información del número de plantas de la casa o algo así
    median_floor = df['floor'].median()
    df['floor'].fillna(median_floor, inplace=True)
    df['floor'] = df['floor'].astype(float)
    
    #los valores perdidos exterior lo voy a imputar por mediana, aunque casi siempre va a ser true
    median_exterior= df['exterior'].median()
    df['exterior'].fillna(median_exterior, inplace=True)
    
    #en cambio, los valores perdidos de hasLift serán false, ya que suelen ser casas o pisos en mal estado que no tienen ascensor
    df['hasLift'].fillna(0, inplace=True)
    
    #asumimos que si no pone que haya piscina es que no la tiene
    df['pool'].fillna(0, inplace=True)
    
    #asumimos que si no pone que haya muebles es que no los tiene
    df['hasFurniture'].fillna(0, inplace=True)
    
    #asumimos que si no pone que sea para estudiantes es que no lo es
    df['forStudents'].fillna(0, inplace=True)
    
    return df




def normalizarVariablesNumericas(train, test):
    boolean_columns = ['hasLift', 'exterior', 'pool', 'hasFurniture','forStudents','newDevelopment']
    all_numeric_columns = ['floor', 'size', 'rooms', 'bathrooms', 'latitude',
                           'longitude', 'hasLift', 'priceByArea',
                           'exterior', 'pool','interest_points']
    
    # Filtrar las columnas numéricas que no son booleanas
    num_feat_not_bool = [col for col in all_numeric_columns if col not in boolean_columns]
    
    # Combinar datos de entrenamiento y prueba para la imputación
    input_all = pd.concat([train.drop('price', axis=1), test])
    skewed_feats = input_all[num_feat_not_bool].apply(lambda x: skew(x.dropna())).sort_values(ascending=False)
    skewness = pd.DataFrame({'Skew' :skewed_feats})
    
    skewed_columns = skewness[abs(skewness['Skew']) > 0.75].index.tolist()
    print("Hay {} variables con skewness para transformar".format(len(skewed_columns)))
    print(skewed_columns)
    # Aplicar logaritmo a las variables con skewness significativa
    # LOG PLUS 1 PARA EVITAR PROBLEMAS CON 0. Práctica común al aplicar transformación logarítmica
    for col in skewed_columns:
        # Comprobar si hay ceros antes de aplicar logaritmo
        if (train[col] == 0).any() or (test[col] == 0).any():
            train[col] += 1  # Sumar 1 a todos los valores para evitar ceros
            test[col] += 1
        if col == 'priceByArea':  # Aplicar Box-Cox a 'priceByArea'
            train[col], _ = boxcox(train[col])
            test[col], _ = boxcox(test[col])
        else:  # Aplicar logaritmo a otras variables con sesgo
            train[col] = np.log1p(train[col])
            test[col] = np.log1p(test[col])
    # Concatenar datos de entrenamiento y prueba después de la normalización y corrección de sesgo
    input_all_normalized = pd.concat([train.drop('price', axis=1), test])

    # Calcular la cantidad de filas y columnas para los subgráficos
    num_variables = len(num_feat_not_bool)
    num_rows = (num_variables + 2) // 3  # Ajuste de la altura
    num_cols = 3  # Mantener 3 columnas para un diseño ordenado

    # Crear la figura y los subgráficos
    fig, axes = plt.subplots(nrows=num_rows, ncols=num_cols, figsize=(16, 4 * num_rows))  # Ajustar la altura

    # Definir paleta de colores personalizada
    custom_palette = sns.color_palette("husl", n_colors=num_variables)

    # Graficar histogramas para cada variable numérica después de la transformación
    for i, col in enumerate(num_feat_not_bool):
        sns.histplot(input_all_normalized[col], ax=axes[i // num_cols, i % num_cols], kde=True, color=custom_palette[i])
        axes[i // num_cols, i % num_cols].set_title(f'Distribución de {col}', fontsize=12)
        axes[i // num_cols, i % num_cols].set_xlabel('')
        axes[i // num_cols, i % num_cols].set_ylabel('')
        sns.despine()

    # Ajustar el diseño
    plt.tight_layout()
    plt.savefig(f"./graficas/distribucionSkewDespuesTransfLog.pdf") #FALTA DISTRIBUCIÓN DE PRICE
    plt.show()
    
    # NORMALIZAR entre 0 y 1
    #usar standard?
    #scaler = StandardScaler()
    scaler = MinMaxScaler()
    train[num_feat_not_bool] = scaler.fit_transform(train[num_feat_not_bool])
    test[num_feat_not_bool] = scaler.fit_transform(test[num_feat_not_bool])
    

    return train, test

def tratadoVariablesCategoricas(df):
    # Tratamos parkingSpace: creamos nuevas variables con las subvariables del JSON, aunque ignoro "parkingSpacePrice", no veo que aporte
    df["isParkingSpaceIncludedInPrice"] = False
    df["hasParkingSpace"] = False
    for index, rows in df.iterrows():
        try:
            df.loc[index, ["isParkingSpaceIncludedInPrice"]] =  eval(rows["parkingSpace"]).get("isParkingSpaceIncludedInPrice")
            df.loc[index, ["hasParkingSpace"]] =  eval(rows["parkingSpace"]).get("hasParkingSpace")
        except:
            pass
        
    # Convertir las columnas a tipo bool
    df["isParkingSpaceIncludedInPrice"] = df["isParkingSpaceIncludedInPrice"].replace({False: 0, True: 1})
    df["hasParkingSpace"] = df["hasParkingSpace"].replace({False: 0, True: 1})

    df.drop("parkingSpace", axis=1, inplace=True)
    
    # Tratamos detailedType: en property type tenemos algo parecido a “typology” así que creo una variable con el subtypology que cubrirá tanto typology 
    # como subtypology. Trato los valores perdidos con propertyType  
    df["subTypology"] = None

    for index, rows in df.iterrows():
        try:
            df.loc[index, ["subTypology"]] =  eval(rows["detailedType"]).get("subTypology")
        except:
            pass
    # Rellenamos los valores perdidos en subTypology con los valores de propertyType

    df['subTypology'] = df['subTypology'].fillna(df['propertyType'])
    df.drop(["detailedType", "propertyType"], axis=1, inplace=True)
    
    
    return df


def crearDummiesyOrdenar (train,test):

    #REVISAR ESTO: input_all podría ponerlo antes de todo el tratado y tratar todo con la combinación y luego separarlo
    #aquí los junto sólo para que se creen las mismas dummies en el conjunto test y entrenamiento aunque no tengan las mismas instancias
    input_all = pd.concat([train.drop('price', axis=1), test])
    
    input_all_dummies = pd.get_dummies(input_all, columns=["subTypology"]) 
    
    # División en características y etiquetas
    y_train = train.price
    X_train = input_all_dummies.iloc[:len(train)]
    train = pd.concat([X_train, y_train], axis=1)
    
    test = input_all_dummies.iloc[len(train):]

    # Ordenar test según la lista de nombres de columnas de train
    column_order = X_train.columns.tolist()
    test = test[column_order]

    return train,test

def dropOutliers (df):
    #búsqueda outliers
    fig, ax = plt.subplots()
    ax.scatter(df['priceByArea'], df['size'])
    plt.ylabel('size', fontsize=13)
    plt.xlabel('priceByArea', fontsize=13)
    plt.savefig(f"./graficas/outliers.pdf")
    plt.show()
    
    #no hay outliers que empeoren el modelo
    #outliers que tienen poca superficie sobre el nivel del suelo y cuestan "mucho" dinero
    # df= df.drop(df[(df['size']>0.7) & (df['priceByArea']>0.6)].index)
    
    return df

def soloGranadaCiudad(df):
    # Seleccionar y modificar las filas donde "municipality" no sea "Granada"
    df = df.loc[df['municipality'] == "Granada"]
    # Eliminar la columna "municipality" después de filtrar
    # df.drop("municipality", axis=1, inplace=True)
    
    return df

def preprocesar(train,test):
    print("Columnas en train ANTES DEL PREPROCESADO:",train.columns)
    # Función principal para el preprocesamiento
    
    train = dropNoFiables(train)
    
    train = eliminarCaracteristicas(train)
    test = eliminarCaracteristicas(test)
    
    train = crearAmenities(train)
    test = crearAmenities(test)
    
    train = tratadoVariablesNumericas(train)
    test = tratadoVariablesNumericas(test)

    train = tratadoVariablesCategoricas(train)
    test = tratadoVariablesCategoricas(test)

    train, test = crearDummiesyOrdenar (train, test)
    
    # for column in train.columns:
    #     print(f"Posibles valores en la columna '{column}':")
    #     unique_values = train[column].value_counts()
    #     #unique_values = train[column].unique()
    #     print(unique_values)
    #     print("\n" + "="*50 + "\n")  # Separador para mayor claridad
    # Crear una figura con múltiples subplots para todas las columnas menos 'price'
    columns_to_plot = [col for col in train.columns if col != 'price']
    num_columns = len(columns_to_plot)
    num_rows = (num_columns + 3) // 4  # Ajusta el número de filas
    num_cols = 4  # Ajusta el número de columnas
    
    fig, axes = plt.subplots(nrows=num_rows, ncols=num_cols, figsize=(20, 4 * num_rows))
    custom_palette = sns.color_palette("husl", n_colors=num_columns)
    
    # Graficar histogramas para cada variable excepto 'price'
    for i, column in enumerate(columns_to_plot):
        sns.histplot(train[column], kde=True, ax=axes[i // num_cols, i % num_cols], color=custom_palette[i])
        axes[i // num_cols, i % num_cols].set_title(f'{column}', fontsize=18, fontweight='bold')
        axes[i // num_cols, i % num_cols].set_xlabel('')
        axes[i // num_cols, i % num_cols].set_ylabel('')
        sns.despine()
    
    # Eliminar subplots vacíos si existen
    for j in range(i + 1, num_rows * num_cols):
        fig.delaxes(axes[j // num_cols, j % num_cols])
    
    # Título general para toda la figura
    fig.suptitle('Distribución de las columnas', fontsize=22, fontweight='bold')
    
    # Ajustar el diseño
    plt.tight_layout(rect=[0, 0, 1, 0.96])  # Deja espacio para el título superior
    plt.savefig(f"./graficas/distribucionCaracteristicas.pdf")
    plt.show()
    
    # Crear una figura para la distribución de 'price'
    fig, ax = plt.subplots(figsize=(10, 6))
    sns.histplot(train['price'], kde=True, color='blue')
    ax.set_title('Distribución de price', fontsize=22, fontweight='bold')
    ax.set_xlabel('')
    ax.set_ylabel('')
    sns.despine()
    
    # Ajustar el diseño y guardar la figura
    plt.tight_layout()
    plt.savefig(f"./graficas/distribucionPrice.pdf")
    plt.show()
    
    train, test= normalizarVariablesNumericas(train, test)

    # train = dropOutliers(train)
    
    return train, test

def visualizarDatos(train):
    # Visualización de la matriz de correlación original - quita las categóricas del cálculo
    corrmat = train.corr()
    f, ax = plt.subplots(figsize=(12, 9))
    sns.heatmap(corrmat, vmax=.8, square=True)

    # Imprimir el número de variables
    num_variables = len(train.columns) - 1  # Excluyendo price
    print(f"Número de variables: {num_variables}")

    # Imprimir el nombre de las variables (excluyendo 'price')
    variable_names = train.drop('price', axis=1).columns
    print("Nombres de las variables:")
    print(variable_names)

    # Calcular la media de correlación con SalePrice
    mean_corr = corrmat['price'].mean()
    print(f"Media de correlación con SalePrice: {mean_corr}")

    # Obtener las columnas con valores nulos y ordenarlas
    cols_with_null = train.isnull().sum().sort_values(ascending=False)
    top_20_cols_with_null = cols_with_null.head(20)
    sns.set(style="whitegrid")
    plt.figure(figsize=(12, 8))
    bar_plot = sns.barplot(x=top_20_cols_with_null.index, y=top_20_cols_with_null, palette="viridis")
    bar_plot.set_xticklabels(bar_plot.get_xticklabels(), rotation=45, horizontalalignment='right')
    plt.title("Ranking Variables con Valores Nulos", fontsize=16)
    plt.xlabel("Variables", fontsize=14)
    plt.ylabel("Número de Valores Nulos", fontsize=14)
    plt.savefig(f"./graficas/rankingNulos.pdf")
    plt.show()

    # Visualización de la correlación entre las 30 variables más correlacionadas con price
    k = 30  # Número de variables para el heatmap
    cols = corrmat.nlargest(k, 'price')['price'].index
    plt.figure(figsize=(25, 25))
    cm = np.corrcoef(train[cols].values.T)
    sns.set(font_scale=1.00)
    hm = sns.heatmap(cm, cbar=True, annot=True, square=True, fmt='.2f', annot_kws={'size': 10}, yticklabels=cols.values, xticklabels=cols.values)
    plt.savefig(f"./graficas/corrconpricemas.pdf")
    plt.show()


    # GRÁFICOS DE DISPERSIÓN, ANÁLISIS DE CARÁCTERÍSTICAS
    features = ['size', 'rooms', 'bathrooms'] 
    for feature in features:
        plt.figure(figsize=(8, 6))
        plt.scatter(train[feature], train['price'])
        plt.title(f'{feature} vs Precio')
        plt.xlabel(feature)
        plt.ylabel('Precio')
        plt.grid(True)
        plt.savefig(f"./graficas/dispersion{feature}.pdf")
        plt.show()




def entrenarModelo(X_train, y_train):
    #hiperparámetros para XGBoost
    xgb_params = {
        'n_estimators': 1150,
        'max_depth': 6,
        'eta': 0.1,
        'subsample': 0.8,
        'colsample_bytree': 0.8,
        'reg_alpha': 0.2,
        'reg_lambda': 1,
        'gamma': 0.2,
        'tree_method': 'exact',
        'objective': 'reg:squarederror'
    }

    #hiperparámetros para LightGBM
    lgb_params = {
        'n_estimators': 1150,
        'learning_rate': 0.1,
        'num_leaves': 31,
        'max_depth': 6,
        'subsample': 0.8,
        'min_data_in_leaf': 60,
        'feature_fraction': 0.8,
        'bagging_fraction': 0.8,
        'bagging_freq': 1,
        'lambda_l1': 0.7,
        'lambda_l2': 0.7,
        'verbose': -1
    }
    
    # Inicializar los modelos con los hiperparámetros definidos
    xgb_model = xgb.XGBRegressor(**xgb_params)
    lgb_model = lgb.LGBMRegressor(**lgb_params)

    # Validación cruzada para los modelos
    kf = KFold(n_splits=10, shuffle=True, random_state=42)

    # Medir el tiempo de ejecución para LightGBM
    start_time_lgb = time.time()
    cv_results_lgb = cross_val_score(lgb_model, X_train, y_train, cv=kf, scoring='neg_mean_squared_error')
    end_time_lgb = time.time()
    time_lgb = end_time_lgb - start_time_lgb
    mean_rmse_lgb = np.sqrt(-cv_results_lgb.mean())
    std_rmse_lgb = cv_results_lgb.std()

    print(f"RMSE (LightGBM, CV 10): {mean_rmse_lgb} +/- {std_rmse_lgb}")
    print(f"Tiempo de ejecución LightGBM: {time_lgb:.2f} segundos")
    
    # Medir el tiempo de ejecución para XGBoost
    start_time_xgb = time.time()
    cv_results_xgb = cross_val_score(xgb_model, X_train, y_train, cv=kf, scoring='neg_mean_squared_error')
    end_time_xgb = time.time()
    time_xgb = end_time_xgb - start_time_xgb
    mean_rmse_xgb = np.sqrt(-cv_results_xgb.mean())
    std_rmse_xgb = cv_results_xgb.std()

    print(f"RMSE (XGBoost, CV 10): {mean_rmse_xgb} +/- {std_rmse_xgb}")
    print(f"Tiempo de ejecución XGBoost: {time_xgb:.2f} segundos")

    # Entrenar los modelos con todos los datos de entrenamiento
    xgb_model.fit(X_train, y_train)
    lgb_model.fit(X_train, y_train)

    # Calcular y imprimir el R^2 en los datos de entrenamiento para XGBoost
    y_train_pred_xgb = xgb_model.predict(X_train)
    r2_train_xgb = r2_score(y_train, y_train_pred_xgb)
    print(f"R^2 conjunto train (XGBoost): {r2_train_xgb}")

    # Calcular y imprimir el R^2 en los datos de entrenamiento para LightGBM
    y_train_pred_lgb = lgb_model.predict(X_train)
    r2_train_lgb = r2_score(y_train, y_train_pred_lgb)
    print(f"R^2 conjunto train (LightGBM): {r2_train_lgb}")

    # Guardar los modelos entrenados
    joblib.dump(xgb_model, 'modelo_xgb_entrenado.pkl')
    joblib.dump(lgb_model, 'modelo_lgb_entrenado.pkl')
    
    
    # Obtener importancia de características por ganancia
    feature_importance_xgb_gain = xgb_model.feature_importances_
    feature_importance_lgb_gain = lgb_model.booster_.feature_importance(importance_type='gain')

    features = X_train.columns

    # Crear DataFrames separados y ordenarlos por importancia
    importance_df_xgb_gain = pd.DataFrame({
        'Feature': features,
        'XGBoost Importance': feature_importance_xgb_gain
    }).sort_values(by='XGBoost Importance', ascending=False)

    importance_df_lgb_gain = pd.DataFrame({
        'Feature': features,
        'LightGBM Importance': feature_importance_lgb_gain
    }).sort_values(by='LightGBM Importance', ascending=False)

    # Combinar los DataFrames manteniendo ambas importancias ordenadas
    importance_df_gain = importance_df_xgb_gain.merge(importance_df_lgb_gain, on='Feature')

    # Graficar importancia de características por ganancia para XGBoost
    plt.figure(figsize=(10, 8))
    plt.barh(importance_df_xgb_gain['Feature'], importance_df_xgb_gain['XGBoost Importance'], color='orange', edgecolor='black')
    plt.xlabel('Importancia')
    plt.ylabel('Características')
    plt.title('Importancia de las Características por su Ganancia (XGBoost)')
    plt.gca().invert_yaxis()
    plt.savefig(f"./graficas/importanceGainXGBoost.pdf")
    plt.show()
    
    # Graficar importancia de características por ganancia para LightGBM
    plt.figure(figsize=(10, 8))
    plt.barh(importance_df_lgb_gain['Feature'], importance_df_lgb_gain['LightGBM Importance'], color='green', edgecolor='black')
    plt.xlabel('Importancia')
    plt.ylabel('Características')
    plt.title('Importancia de las Características por su Ganancia (LightGBM)')
    plt.gca().invert_yaxis()
    plt.savefig(f"./graficas/importanceGainLGBM.pdf")
    plt.show()

    # Obtener importancia de características por su peso (frecuencia)
    importance_type = 'weight'
    feature_importance_xgb_weight = xgb_model.get_booster().get_score(importance_type=importance_type)
    feature_importance_lgb_weight = lgb_model.booster_.feature_importance(importance_type='split')

    importance_df_xgb_weight = pd.DataFrame({
        'Feature': list(feature_importance_xgb_weight.keys()),
        'XGBoost Importance': list(feature_importance_xgb_weight.values())
    }).sort_values(by='XGBoost Importance', ascending=False)

    importance_df_lgb_weight = pd.DataFrame({
        'Feature': features,
        'LightGBM Importance': feature_importance_lgb_weight
    }).sort_values(by='LightGBM Importance', ascending=False)

    # Graficar importancia de características para XGBoost por peso
    plt.figure(figsize=(10, 8))
    plt.barh(importance_df_xgb_weight['Feature'], importance_df_xgb_weight['XGBoost Importance'], color='orange', edgecolor='black')
    plt.xlabel('Importancia')
    plt.ylabel('Características')
    plt.title('Importancia de las Características por su Peso (XGBoost)')
    plt.gca().invert_yaxis()
    plt.savefig(f"./graficas/importanceWeightXGBoost.pdf")
    plt.show()

    # Graficar importancia de características para LightGBM por peso
    plt.figure(figsize=(10, 8))
    plt.barh(importance_df_lgb_weight['Feature'], importance_df_lgb_weight['LightGBM Importance'], color='green', edgecolor='black')
    plt.xlabel('Importancia')
    plt.ylabel('Características')
    plt.title('Importancia de las Características por su Peso (LightGBM)')
    plt.gca().invert_yaxis()
    plt.savefig(f"./graficas/importanceWeightLGBM.pdf")
    plt.show()


    # Curvas de aprendizaje
    plot_learning_curve(xgb_model, X_train, y_train, title="Curva de Aprendizaje para XGBoost")
    plot_learning_curve(lgb_model, X_train, y_train, title="Curva de Aprendizaje para LightGBM")

    return xgb_model, lgb_model


def combinarPredicciones(models, X_test):
    #pesos de los modelos (más peso a XGBoost)
    pesos = np.array([0.7, 0.3])
    preds = np.zeros((X_test.shape[0], len(models)))
    for i, model in enumerate(models):
        preds[:, i] = model.predict(X_test)
    # Promedio ponderado de las predicciones
    final_pred = np.dot(preds, pesos)
    return final_pred


def plot_learning_curve(model, X, y, title="Curva de Aprendizaje"):
    plt.figure(figsize=(14, 8))
    
    # Obtenemos los valores de entrenamiento y validación con diferentes tamaños de datos de entrenamiento
    train_sizes, train_scores, val_scores = learning_curve(model, X, y, cv=10, scoring='neg_mean_squared_error', n_jobs=-1, train_sizes=np.linspace(0.1, 1.0, 10))

    # Calcular la media y la desviación estándar para las curvas de entrenamiento y validación
    train_scores_mean = np.mean(np.sqrt(-train_scores), axis=1)
    train_scores_std = np.std(np.sqrt(-train_scores), axis=1)
    val_scores_mean = np.mean(np.sqrt(-val_scores), axis=1)
    val_scores_std = np.std(np.sqrt(-val_scores), axis=1)
    
    # Graficar las curvas de aprendizaje
    plt.plot(train_sizes, train_scores_mean, 'o-', color='orange', label='Error en Entrenamiento')
    plt.plot(train_sizes, val_scores_mean, 'o-', color='blue', label='Error en Validación')
    
    # Sombreado para la desviación estándar
    plt.fill_between(train_sizes, train_scores_mean - train_scores_std, train_scores_mean + train_scores_std, alpha=0.2, color='orange')
    plt.fill_between(train_sizes, val_scores_mean - val_scores_std, val_scores_mean + val_scores_std, alpha=0.2, color='blue')
    
    plt.title(title, fontsize=20, weight='bold')
    plt.xlabel('Tamaño del conjunto de entrenamiento', fontsize=15)
    plt.ylabel('RMSE', fontsize=15)
    plt.legend(loc='best', fontsize=12)
    plt.grid(True)
    plt.tight_layout()
    plt.savefig(f"./graficas/curva_aprendizaje_{model.__class__.__name__}.pdf")
    plt.show()



def visualizarPredicciones(y_test, pred):
    # Calcular RMSE, R2 y MAE de test
    rmse = np.sqrt(mean_squared_error(y_test, pred))
    r2 = r2_score(y_test, pred)
    mae = mean_absolute_error(y_test, pred)
    
    print(f"RMSE conjunto test: {rmse}")
    print(f"R^2 conjunto test: {r2}")
    print(f"MAE conjunto test: {mae}")
    

   
    fig, ax = plt.subplots(figsize=(8, 6))
    ax.scatter(y_test, pred, color='orange', label='Predicciones', alpha=0.7, s=20)
    ax.plot([y_test.min(), y_test.max()], [y_test.min(), y_test.max()], color='black', linestyle='--', label='Perfecta predicción')
    ax.set_xlabel('Precios Reales', fontsize=18)
    ax.set_ylabel('Precios Predichos', fontsize=18)
    ax.set_title('Predicciones vs. Precios Reales', fontsize=20, weight= 'bold')
    ax.legend()
    ax.margins(0.1)
    plt.tight_layout()
    plt.savefig(f"./graficas/graficapreciccionprecios.pdf")
    plt.show()
    
    # Crear los rangos de valores
    min_value = min(y_test.min(), pred.min())
    max_value = 1300
    bins = np.linspace(min_value, max_value, 6)  # 5 intervals, 6 edges
    y_test_binned = np.digitize(y_test, bins) - 1
    pred_binned = np.digitize(pred, bins) - 1

    # Crear la matriz de confusión adaptada
    matrix = np.zeros((5, 5))

    for true_bin, pred_bin in zip(y_test_binned, pred_binned):
        if 0 <= true_bin < 5 and 0 <= pred_bin < 5:
            matrix[true_bin, pred_bin] += 1

    # Crear un DataFrame para usar en seaborn
    bin_labels = [f'{bins[i]:.2f}-{bins[i+1]:.2f}' for i in range(5)]
    matrix_df = pd.DataFrame(matrix, index=bin_labels, columns=bin_labels)

    # Configurar la visualización
    plt.figure(figsize=(14, 12))
    sns.set(font_scale=1.5)  # Aumentar el tamaño de la fuente
    sns.set_style("whitegrid")
    
    ax = sns.heatmap(matrix_df, annot=True, fmt='.0f', cmap='Oranges', cbar=False, linewidths=1, linecolor='gray', annot_kws={"size": 30, "weight": "bold"})
    
    ax.set_xlabel('Predicciones', fontsize=25, weight='bold')
    ax.set_ylabel('Valores Reales', fontsize=25, weight='bold')
    ax.set_title('Matriz de Confusión para Regresión (Rangos)', fontsize=30, weight='bold')
    
    # Añadir la línea de la predicción perfecta
    plt.plot([0, 5], [0, 5], color='black', linestyle='--', linewidth=2.5, label='Predicción Perfecta')
    plt.legend(fontsize=16)

    plt.tight_layout()
    plt.savefig("./graficas/matriz_confusion_regresion.pdf")
    plt.show()

    # Crear los rangos de valores
    min_value = min(y_test.min(), pred.min())
    max_value = 1300
    bins = np.linspace(min_value, max_value, 5)  # 4 intervals, 5 edges
    y_test_binned = np.digitize(y_test, bins) - 1
    pred_binned = np.digitize(pred, bins) - 1

    # Crear la matriz de confusión adaptada
    matrix = np.zeros((4, 4))

    for true_bin, pred_bin in zip(y_test_binned, pred_binned):
        if 0 <= true_bin < 4 and 0 <= pred_bin < 4:
            matrix[true_bin, pred_bin] += 1

    # Crear un DataFrame para usar en seaborn
    bin_labels = [f'{bins[i]:.2f}-{bins[i+1]:.2f}' for i in range(4)]
    matrix_df = pd.DataFrame(matrix, index=bin_labels, columns=bin_labels)

    # Configurar la visualización
    plt.figure(figsize=(14, 12))
    sns.set(font_scale=1.5)  # Aumentar el tamaño de la fuente
    sns.set_style("whitegrid")
    
    ax = sns.heatmap(matrix_df, annot=True, fmt='.0f', cmap='Oranges', cbar=False, linewidths=1, linecolor='gray', annot_kws={"size": 30, "weight": "bold"})
    
    ax.set_xlabel('Predicciones', fontsize=25, weight='bold')
    ax.set_ylabel('Valores Reales', fontsize=25, weight='bold')
    ax.set_title('Matriz de Confusión para Regresión (Rangos)', fontsize=30, weight='bold')
    
    # Añadir la línea de la predicción perfecta
    plt.plot([0, 4], [0, 4], color='black', linestyle='--', linewidth=2.5, label='Predicción Perfecta')
    plt.legend(fontsize=16)

    plt.tight_layout()
    plt.savefig("./graficas/matriz_confusion_regresion4x4.pdf")
    plt.show()
    
    graficaDistribucionErrores(y_test, pred)
    graficaResiduosVsPredicciones(y_test, pred)

def graficaDistribucionErrores(y_test, pred):
    errores = y_test - pred
    plt.figure(figsize=(10, 6))
    sns.histplot(errores, kde=True, color='orange')
    plt.xlabel('Errores', fontsize=15)
    plt.ylabel('Frecuencia', fontsize=15)
    plt.title('Distribución de Errores', fontsize=18, weight='bold')
    plt.tight_layout()
    plt.savefig("./graficas/distribucion_errores.pdf")
    plt.show()
    
def graficaResiduosVsPredicciones(y_test, pred):
    errores = y_test - pred
    plt.figure(figsize=(10, 6))
    plt.scatter(pred, errores, color='orange', alpha=0.6)
    plt.axhline(y=0, color='black', linestyle='--', linewidth=2)
    plt.xlabel('Predicciones', fontsize=18)
    plt.ylabel('Errores', fontsize=18)
    plt.title('Residuos vs. Predicciones', fontsize=20, weight='bold')
    plt.tight_layout()
    plt.savefig("./graficas/residuos_vs_predicciones.pdf")
    plt.show()


def guardarResultados(test_urls, pred):
    # Guardar resultados
    salida = pd.DataFrame({'url': test_urls, 'price': pred})
    salida.to_csv("./resultado/prediccionRent.csv", index=False)






