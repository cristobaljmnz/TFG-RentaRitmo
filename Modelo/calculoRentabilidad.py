#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Apr 24 18:13:20 2024

@author: cristobaljimenez_
"""

import pandas as pd


import pandas as pd
import numpy as np

def calcular_gastos_registro(valor_catastral):
    if valor_catastral <= 6010.12:
        return 24.04
    elif valor_catastral <= 30050.61:
        return 24.04 + ((valor_catastral - 6010.13) / 1000) * 1.75
    elif valor_catastral <= 60101.21:
        return 74.78 + ((valor_catastral - 30050.62) / 1000) * 1.25
    elif valor_catastral <= 150253.03:
        return 199.03 + ((valor_catastral - 60101.22) / 1000) * 0.75
    elif valor_catastral <= 601012.10:
        return 430.28 + ((valor_catastral - 150253.04) / 1000) * 0.30
    else:
        return 1642.91 + ((valor_catastral - 601012.10) / 1000) * 0.20

def calcular_ITPoIVA(row):
    if row['New_Development'] == 0 or row['New_Development'] == 0.0:
        if row['Precio_venta'] < 150000:
            itp_iva = row['Precio_venta'] * 0.072
        else:
            itp_iva = row['Precio_venta'] * 0.082

    if row['New_Development'] == 1 or row['New_Development'] == 1.0:
        itp_iva = row['Precio_venta'] * 0.1

    return itp_iva

def ponderar_notaria(valor, min_val, max_val, valor_min_notaria=270, valor_max_notaria=500):
    if min_val == max_val:  # Caso especial si todos los tamaños de viviendas son iguales
        return valor_min_notaria
    return valor_min_notaria + (valor - min_val) * (valor_max_notaria - valor_min_notaria) / (max_val - min_val)


def calcular_gastos_operativos(datos):
    #SEGÚN AGENTE INMOBILIARIO + APP IDEALISTA 
    datos['IBI'] = datos['Prediccion_Alquiler'] * 0.55
    datos['seguros'] = datos['Prediccion_Alquiler'] * 0.384
    datos['mantenimiento'] = datos['Prediccion_Alquiler'] * 0.54
    datos['comunidad'] = datos.apply(lambda row: row['Prediccion_Alquiler'] * 0.6 if row['es_casa'] == 1 else row['Prediccion_Alquiler'] * 1.2, axis=1)

    datos['IBI_mensual'] = datos['IBI'] / 12
    datos['seguros_mensual'] = datos['seguros'] / 12
    datos['mantenimiento_mensual'] = datos['mantenimiento'] / 12
    datos['comunidad_mensual'] = datos['comunidad'] / 12

    datos['gastos_operativos_anuales'] = datos['IBI'] + datos['seguros'] + datos['comunidad'] + datos['mantenimiento']
    datos['gastos_operativos_mensuales'] = datos['IBI_mensual'] + datos['seguros_mensual'] + datos['comunidad_mensual'] + datos['mantenimiento_mensual']
    return datos

def calcular_hipoteca(datos, TAE=0.034, anios=30):

    #ponemos que TAE es 3,4% (tasa anual nominal) -> según el tipo francés obtenemos el interés mensual haciendo TAE/12 = 0,28333%
    #C = (P * i * (1 + i)^n) / ((1 + i)^n - 1)
    # C: Monto de la cuota (pago periódico constante).
    # P: Capital prestado (monto del préstamo).
    # i: Tasa de interés por período.
    # n: Número total de cuotas.
    interes_mensual = TAE / 12
    num_cuotas = anios * 12

    datos['hipoteca'] = datos['Precio_venta'] * 0.6
    datos['inversion_inicial'] = (datos['coste_total'] - datos['hipoteca']).round().astype(int)

    datos['cuota_hip_mensual'] = (datos['hipoteca'] * interes_mensual * (1 + interes_mensual)**num_cuotas) / ((1 + interes_mensual)**num_cuotas - 1)
    datos['cuota_hip_anual'] = datos['cuota_hip_mensual'] * 12
    #interes promedio
    #mensual = ((cuota hip mensual * num_cuotas)-hipoteca) / num_cuotas
    datos['interes_medio_mensual'] = ((datos['cuota_hip_mensual'] * num_cuotas) - datos['hipoteca']) / num_cuotas
    datos['interes_medio_anual'] = datos['interes_medio_mensual'] * 12

    datos['amortizacion_media_mensual'] = datos['cuota_hip_mensual'] - datos['interes_medio_mensual']
    datos['amortizacion_media_anual'] = datos['amortizacion_media_mensual'] * 12
    return datos

def calcular_flujo_caja(datos):
    datos['cashflow_anual'] = datos['alquiler_anual'] - datos['cuota_hip_anual'] - datos['gastos_operativos_anuales']
    datos['cashflow_mensual'] = datos['Prediccion_Alquiler'] - datos['cuota_hip_mensual'] - datos['gastos_operativos_mensuales']
    return datos

def calcular_rentabilidad(datos):
    datos['rentabilidad_bruta'] = datos['alquiler_anual'] / datos['coste_total']
    datos['rentabilidad_neta'] = (datos['alquiler_anual'] - datos['gastos_operativos_anuales'] - datos['interes_medio_anual']) / datos['coste_total']

    datos['hip/alq'] = datos['cuota_hip_mensual'] / datos['Prediccion_Alquiler']
    datos['cashflow/alq'] = datos['cashflow_mensual'] / datos['Prediccion_Alquiler']

    datos['ROCE'] = (datos['cashflow_anual'] + datos['amortizacion_media_anual']) / datos['inversion_inicial']
    
    datos['payback_period'] = datos['inversion_inicial'] / datos['cashflow_anual']
    return datos

def calcular_datos_rentabilidad():
    # Cargar datos
    datos = pd.read_csv("./resultado/datosParaCalcularRentabilidad.csv")

    # Calcular el alquiler anual estimado del inmueble
    datos['alquiler_anual'] = datos['Prediccion_Alquiler'] * 12
    # Calcular los gastos operativos
    datos = calcular_gastos_operativos(datos)

    # Estimar valor catastral
    tipo_impositivo = 0.00639
    datos['valor_catastral'] = datos['IBI'] / tipo_impositivo

    # Calcular ITPoIVA
    datos['ITPoIVA'] = datos.apply(calcular_ITPoIVA, axis=1)

    
    min_val = datos['size_vivienda'].min()
    max_val = datos['size_vivienda'].max()
    datos['notaria'] = datos['size_vivienda'].apply(ponderar_notaria, args=(min_val, max_val))

    # Calcular gastos de registro
    datos['registro'] = datos.apply(lambda row: calcular_gastos_registro(row['valor_catastral']), axis=1)
    #gastos de agencia inmobiliaria (estándar)
    datos['agencia_inmobiliaria'] = datos['Precio_venta'] * 0.03
    #gastos de compra
    datos['gastos_compra'] = datos['ITPoIVA'] + datos['notaria'] + datos['registro'] + datos['agencia_inmobiliaria']
    #coste total inmueble
    datos['coste_total'] = datos['Precio_venta'] + datos['gastos_compra']

    datos = calcular_hipoteca(datos)
    datos = calcular_flujo_caja(datos)
    datos = calcular_rentabilidad(datos)
    
    datos.to_csv("./resultado/rentabilidad_inversion_todos.csv")

    #todo esto se precalcula porque necesito un primer calculo para meterlo en la base de datos 
    #para poder filtrar por estos valores u ordenar por ellos
    datos_viviendas = pd.read_csv("./resultado/datosViviendas.csv")
    
    # Agregar las nuevas columnas
    datos_viviendas['rentabilidad_bruta'] = datos['rentabilidad_bruta']
    datos_viviendas['cashflow_mensual'] = datos['cashflow_mensual']
    datos_viviendas['inversion_inicial'] = datos['inversion_inicial']
    datos_viviendas['payback_period'] = datos['payback_period']
    

    datos_viviendas.to_csv("./resultado/datosViviendas.csv", index=False)
    
if __name__ == "__main__":
    calcular_datos_rentabilidad()


