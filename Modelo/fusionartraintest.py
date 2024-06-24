#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Apr 19 02:19:58 2024

@author: cristobaljimenez_
"""

import base64
import requests as rq
import json
import pandas as pd
import time
import rentPriceRegressorFunciones
#CAMBIAR LOCALIZACIÓN ARCHIVOS



train = pd.read_csv("./datasetsRent/train_dataset_interest_points.csv", na_values="NaN")
test = pd.read_csv("./datasetsRent/test_dataset_interest_points.csv", na_values="NaN")

train = pd.concat([train,test])

train.to_csv('./datasetsRent/fusionado_con_interest_points.csv', index=True)
