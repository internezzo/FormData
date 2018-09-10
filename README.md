# Internezzo.FormData

## What it does
This package provides a finisher to save form data into the db. The data gets stored formData-records which are grouped by collector-records.

## Usage

### Backend module
In the backend module you can create/delete collectors. These are needed as destination for the finishers.
If a collector has data (a finisher has send it), you can download the data as csv.
You can also show the the list of formdata for an collector and delete a single formdata entry. 

### Form-Builder (nodebased forms)
1. Create a collector record in the backend module.
2. Add a FormData Finisher to your form and select the collector
3. To prevent uuid as data keys give all form elements a speaking identifier
4. Submitted forms should not populate formdata-records of the collector

### Forms without nodes
Not testet yet, but should work just by adding the finisher and setting the option "collecor" to an uuid-identifier of a collector record.

## Installation
    composer require internezzo/formdata

## Authorisation
Per default only the role Neos.Neos:Administrator is allowed to see the module, create and delete Collectors. You can allow other roles to perform these acttion by granting them the privileges. Have a look into the policy.yaml of this package to see the available privileges.

## Exclude form element types
This is especially useful to prevent captcha form element data from being saved.
In your settings:

    Internezzo:
      FormData:
        skipElementTypes:
          'Internezzo.Neos:Captcha': TRUE