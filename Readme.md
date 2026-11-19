#Loyalty

> **Deprecated.** CreditAccount 4.0 earns credit on orders by itself: tiers, a percentage of the order, excluded products and categories, and the status that triggers the earning. Use it instead of this module.
>
> CreditAccount 4.0 takes over the tiers of this module when it is updated (the single slice mode becomes a percentage of the order: the credit divided by the slice, rounded to two decimals), and earns nothing by itself while Loyalty is active. Deactivate Loyalty once you have checked the loyalty settings in the CreditAccount configuration. This module gets no further release.

With this module, you can reward the loyalty of your customers.

## Installation

**This module require [Credit account](https://github.com/thelia-modules/CreditAccount) module.**

with git

```
$ cd local/modules
$ git clone https://github.com/thelia-modules/Loyalty
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
    
## version history

0.6 : single slice mode added



