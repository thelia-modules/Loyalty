#Loyalty

This module allow you to manage

## Installation

with git

```
$ cd local/modules
$ git clone https://github.com/thelia-modules/CreditAccount
```

If you want to download the zip from github, rename the unzip folder as ```Loyalty```, github suffix the zip and the folder with the current branch name.

After that, you just have to activate the module in your back-office.

## How to use it

Once activated, go to the configuration page for this module and configure all the slice price you want.

After each order, the customer will receive the amount you configure in his credit account.

## Loops

One loop exists in this module. This loop can list all slice configure in the admin. So you can create a page for explain to your customers
The rules for having some more credits.

### loyalty loop

**input argument**

None

**output arguments**

    * $ID : loyalty slice id
    * $MIN : minimum order amount for this slice
    * $MAX : maximum order amount for this slice
    * $AMOUNT : amount added to the customer's credit account



