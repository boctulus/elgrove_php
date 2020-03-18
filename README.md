# SimpleRestFul

## Request examples

## GET <READ>

    GET /api/v1/products
    GET /api/v1/products/83

### Search    

    GET /api/v1/products?name=Vodka
    GET /api/v1/products?name=Vodka&size=1L

IN / NOT IN

    GET /api/v1/products?name=Vodka,Wisky,Tekila
    GET /api/v1/products?name[in]=Vodka,Wisky,Tekila
    GET /api/v1/products?name[notIn]=CocaCola,7up

### String comparisons   

    contains 
    notContains 
    startsWith  
    notStartsWith
    endsWith   
    notEndsWith
    
Example

    GET /api/v1/products?name[contains]=jugo 

### Other comparators

    =    eq
    !=   neq
    >    gt
    <    lt
    >=   gteq
    <=   lteq

Example:  
    
    GET /api/v1/products?cost[gteq]=25&cost[lteq]=100

### BETWEEN

    GET /api/v1/products?order[cost]=ASC&cost[between]=200,300
    GET /api/v1/products?created_at[between]=2019-10-15 00:00:00,2019-09-01 23:59:59

### List of fields to include

    GET /api/v1/products?fields=id,name,cost
    GET /api/v1/products/83?fields=id,name,cost
    GET /api/v1/products?fields=id,cost&name=Vodka

### Exclude fields

    GET /api/v1/users?exclude=firstname,lastname

### Select null or not null values

    GET /api/v1/products?description=NULL
    GET /api/v1/products?description[neq]=NULL

# Pagination

### ORDER BY

    GET /api/v1/products?order[cost]=DESC
    GET /api/v1/products?order[cost]=DESC&order[name]=ASC
    GET /api/v1/products?order[cost]=ASC&order[id]=DESC

### LIMIT

    GET /api/v1/products?limit=10
    GET /api/v1/products?offset=40&limit=10
    GET /api/v1/products?limit=10&order[name]=ASC&order[cost]=DESC&size=2L

Pagination can be done with page and pageSize

    GET /api/v1/products?page=3
    GET /api/v1/products?pageSize=20&page=2
    
### Pretty print 

    GET /api/v1/products?pretty

By default pretty print can be enabled or disabled in config/config.php    

## POST <CREATE>

    POST /api/v1/products

    {
        "name": "Vodka",
        "description": "from Bielorussia",
        "size": "2L",
        "cost": "200"
    }


## DELETE

    DELETE /api/v1/products/100

A record can be effectly deleted in one shot from database or if soft-delete is enabled then be marked as deleted in which case it will be seen as deleted as well.

When a record is softly deleted then it can be seen at TrashCan where is posible to delete it permanently or to be recovered.

## PUT  <UPDATE>

    PUT /api/v1/products/84

    {
        "name": "Vodka",
        "description": "from Russia",
        "size": "2L",
        "cost": "200"
    }


## PATCH <PARTIAL UPDATE>

    PUT /api/v1/products/84

    {
        "description": "from Mongolia",
        "cost": "230"
    }

# /me

The simple way to perform CRUD operations on the current user is using /api/v1/me endpoint.

    GET /api/v1/me

    {
        "data": {
            "id": "4",
            "username": "pbozzolo",
            "email": "pbozzolo@gmail.com",
            "confirmed_email": "1",
            "firstname": "Paulinoxxxy",
            "lastname": "Bozzoxxxy",
            "deleted_at": null,
            "belongs_to": "0"
        },
        "error": "",
        "error_detail": ""
    }

