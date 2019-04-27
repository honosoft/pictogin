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

## How to run

Go in the ``/configs`` directory and configure the ``*.sample.php`` files by removing the ``.sample'``.

Start the servers:
```
docker-compose run composer install
docker-compose up
```

launch the url: ``http://localhost:8500/``


## TODO
* UI
  [x] click on image and register it for the next action - or better, go to next page onclick
  [x] make the refresh not increment the current page... or at least, reset it.
  [ ] implement all buttons.
  [ ] Still bug when press F5 in login.
  [ ] Do a /signup that uses the  its own controller.
* unsplash
  [x] register an unsplash account.
  [x] implement the code.
  [ ] register write the documentation.
* find a common size?
* Make a reset password.
* Make a homepage (anonymous) that explains the project and link the GIT.
- [x] 