# Zendesk AJAX API
+ Author: **orta.sanz.93@gmail.com** (MyTinySecrets Dev)
+ API Client Doc: [GitHub](https://github.com/zendesk/zendesk_api_client_php)

# Before start development
Before you start using this API you will need to install all dependences using [Composer](https://getcomposer.org/).
Inside **src** folder you will need to use the following command in order to download all packages:

``` ubuntu
$ composer install
```

### API Credentials

*Zendesk* uses a [Access and sign-in configuration](https://support.zendesk.com/hc/en-us/articles/203663776-Configuring-how-end-users-access-and-sign-in-to-your-Zendesk) for this API to work you will need the **subdomain**, **username**, and the **[Token](https://support.zendesk.com/hc/en-us/articles/203663776-Configuring-how-end-users-access-and-sign-in-to-your-Zendesk)**.

Once you have all the neccesary data you will need to create a new `config.yml` inside `src` folder (you can use `config.yml.dist` template)

# Allow users to use this feature.
Zendesk does not allow normal users (*end-users*) to use the API if their e-mail is not verified, to verify an user email we need to head to Zendesk panel and find the user we want to verify, at the user panel we can see the user's email and a flag which warns us that the current user email is not verified yet, at the image below you can see how to verify:

![Verify](https://dl.dropboxusercontent.com/u/37507878/email.png "Verify")

After that the user can use the API properly.
