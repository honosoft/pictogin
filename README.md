# pictogin
A login using pictures instead of alphanumeric characters.

## Description
The idea is to try and avoid to type a password and having a DB full of 'qwertyasdf' and '1234567'. 
Instead of relying on the creativity of creating a passwords with tons of constraint difficult to
remember, I want to use the pattern recognition function of our brain, which is probably
what our brain was designed for.

From that idea, the goal is to present a series of images, from which the user choosed N images and 
have to figure out which one it is from a list of M images. Also, to increase the security, depending
on the randomness, the image might not appear at all from the list.

Another issue, that I will not tackle from this small project, is the possibility of altering the images
and simplify them using an image filter. The goal is to really use the power of our brain to recognize
the parttern.

Another thing that I though is that we should only give X number of retries, in case someone is trying
to brute force the algorithm. That why, I will lock the login flow and use the "send magic link" to
log back to the service.


## Development

### How to run

Go in the ``/configs`` directory and configure the ``*.sample.php`` files by removing the ``.sample'``.

Start the servers:
```
docker-compose run composer install
docker-compose up
```

launch the url: ``http://localhost:8500/``

### How to build

```
docker-compose run composer run-script build
```


## TODO
* Flow
  * Signup (create account)
    - [x] Create account in DB
    - [x] Send an e-mail
  - [x] Login - Modified version.
* UI
  - [x] click on image and register it for the next action - or better, go to next page onclick
  - [x] make the refresh not increment the current page... or at least, reset it.
  - [ ] Still bug when press F5 in login (check if ctrf token fix it).
  - [x] Do a /signup that uses the  its own controller.
* unsplash
  - [x] register an unsplash account.
  - [x] implement the code.
  - [ ] ask for a pro account.
  - [ ] register - write the documentation in readme.
* find a common size?
  - [x] check if we really need to have a common image size (note: unsplash can resize/crop at will)
* Make a reset password.
  - [x] Implement a mail class
  - [x] Use twig templates
  - [ ] Send the actual mail
* E-Mail.
  - [x] Test with mailler on Dreamhost
  - [x] Send a mail on registration.
  - [ ] Send a mail on forget password.
* Add content
  - [ ] Make a homepage (anonymous) that explains the project and link the GIT/honosoft
  - [ ] When logged in, give some more explanation. Add a cute diagram. 
* Deployment
  - [x] Check if we can deploy using composer.
  - [x] Check if I can add the sftp information and upload directly? https://github.com/emmanuelroecker/php-sync-sftp
* Data (optional)
  - [ ] Log ips in database. At least the last 5 or 10 different Ips.
* DB
  - [ ] Add a .sql in a docker folder for the init.d
  - [ ] Use my Simple ORM?
* Layout
  - [ ] Use the https://semantic-ui.com/examples/homepage.html for the main layout.
  - [ ] Finish the layout for the thank you.
* Security
  - [ ] Ensure the CTRF token is activated. 
 * Localization
  - [ ] Use i17n for text in twig or in file.
  
## Bugs
- [ ] When account locked, can still login.
- [ ] Search for todos and if not done, add them to the gitlab.
- [ ] Log out page does not display properly. Did a fix but untested. 