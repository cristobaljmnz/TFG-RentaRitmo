#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Feb  1 23:22:25 2024

@author: cristobaljimenez_
"""

# Complete request example
# curl -X POST -H "Authorization: Basic YWJjOjEyMw==" -H "Content-Type:
# application/x-www-form-urlencoded" -d 'grant_type=client_credentials&scope=read'
# "https://api.idealista.com/oauth/token" -k


import base64
import requests as rq
import json
import pandas as pd
import time
import os

def get_oauth_token():
    api_key =   # PONER TU API KEY provided by Idealista
    secret =  # PONER TU API KEY provided by Idealista
    # Combine the API key and the secret to get a personalized message
    message = api_key + ":" + secret
    auth = "Basic " + base64.b64encode(message.encode("utf-8")).decode("utf-8")  # Codificar el mensaje

    headers_dic = {"Authorization": auth, "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"}

    params_dic = {"grant_type": "client_credentials", "scope": "read"}

    r = rq.post("https://api.idealista.com/oauth/token", headers=headers_dic, params=params_dic)
    token = json.loads(r.text)['access_token']  # Obtain the personalized token, as a JSON
    return token

def fetch_idealista_data(operation, base_dir):
    base_url = 'https://api.idealista.com/3.5/'  # Base search URL
    country = 'es'  # Search country (es, it, pt)
    language = 'es'  # Search language (es, it, pt, en, ca)
    max_items = '50'  # Max items per call, the maximum set by Idealista is 50
    property_type = 'homes'  # Type of property (homes, offices, premises, garages, bedrooms)
    order = 'priceDown'  # Order of the listings, consult documentation for all the available orders
    center = '37.179580,-3.599720'  # Coordinates of the search center
    distance = '6000'  # Max distance from the center
    sort = 'desc'  # How to sort the found items
    maxprice = '9999999'  # Max price of the listings

    url = (base_url +
           country +
           '/search?operation=' + operation +
           '&maxItems=' + max_items +
           '&order=' + order +
           '&center=' + center +
           '&distance=' + distance +
           '&country='+ country +
           '&propertyType=' + property_type +
           '&sort=' + sort +
           '&numPage=%s' +
           '&maxPrice=' + maxprice +
           '&language=' + language)

    def search_api(url):
        token = get_oauth_token()
        headers = {'Content-Type': 'Content-Type: multipart/form-data;', 'Authorization': 'Bearer ' + token}
        content = rq.post(url, headers=headers)
        result = json.loads(content.text)
        return result

    pagination = 1
    first_search_url = url % (pagination)

    results = search_api(first_search_url)

    total_pages = results['totalPages']
    print('total_pages=', total_pages)

    df = pd.DataFrame.from_dict(results['elementList'])
    df.to_csv(os.path.join(base_dir, "busca1.csv"), index=False)  # Save page 1

    concatB = pd.read_csv(os.path.join(base_dir, "busca1.csv"))
    page = 2

    while page <= total_pages + 1:
        search_url = url % (page)
        try:
            results = search_api(search_url)
            df = pd.DataFrame.from_dict(results['elementList'])
            df.to_csv(os.path.join(base_dir, f"busca{page}.csv"), index=False)  # Save all other pages if they exist
            time.sleep(2)
            if concatB is None:
                concatB = df
            else:
                concatB = pd.concat([df, concatB])
            page += 1
        except Exception as e:
            print(f"An error occurred: {e}")
            break
    
    concatB.to_csv(os.path.join(base_dir, 'datos_idealistaNuevos.csv'), index=True)
