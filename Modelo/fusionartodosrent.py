#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Mon Apr 22 17:36:50 2024

@author: cristobaljimenez_

"""

import pandas as pd
import os

def fusionar_csv(path_viejo, path_nuevo):
    try:
        # Leer los archivos CSV
        viejo = pd.read_csv(path_viejo, na_values="NaN")
        nuevo = pd.read_csv(path_nuevo, na_values="NaN")

        print("Número de filas en el archivo nuevo:", len(nuevo))
        print("Número de filas en el archivo viejo:", len(viejo))

        # Filtrar las filas en 'nuevo' que no están en 'viejo' basadas en 'propertyCode'
        nuevo_unicos = nuevo[~nuevo['propertyCode'].isin(viejo['propertyCode'])]

        print("Número de nuevas filas únicas a añadir:", len(nuevo_unicos))

        # Fusionar el DataFrame filtrado con los datos de viejo
        fusion = pd.concat([viejo, nuevo_unicos])

        print("Número total de filas después de la fusión:", len(fusion))

        # Guardar el resultado en el archivo original 'viejo', para futuras actualizaciones
        fusion.to_csv(path_viejo, index=False)

        print("Fusión completada con éxito.")

    except Exception as e:
        print(f"Error durante la fusión de los archivos CSV: {e}")
# Ejemplo de uso
# fusionar_csv('./datasetsRent/fusionado_con_interest_points.csv', './datasetsRent/nuevo_con_interest_points.csv')
