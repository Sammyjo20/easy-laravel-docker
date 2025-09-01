## Easy Laravel Docker
This project aims to give you an easy way to deploy your Laravel application to a Docker-based host super quickly. The image and Dockerfile used is all production ready and ready to serve traffic directly or via a proxy (recommended). This is made for people wanting to host Laravel on their Homelabs or just simply on a server with Docker on.

> [!NOTE]
> Please note that this package is still new and probably has parts which can be improved. If you do find anything, please contribute by opening a pull-request!

### Prerequisites
This project assumes you have some basic to intermediate knowledge of Docker. This project also uses the [serversideup/php](https://serversideup.net/open-source/docker-php/) images so you should refer to their documentation when modifying the Dockerfile.

### Installation
To get started, install the package via Composer using the command below.

```
composer require sammyjo20/easy-laravel-docker --dev
```
> Requires PHP 8.4+ and Laravel 12+

### Getting Started
Once the package has been installed, you can run the command below. This will ask you a few questions like which database engine you would like to use. If you are using a separate database in production, you can choose "None".

```
php artisan install:docker
```

####  Application Name
One of the first questions the command will ask you is your application name. This name will be used to define the name of the image used.

#### Web Port
This will be the port that the web service will run on the host machine. We recommend using a port other than port 80 and 443.

#### Environment
The docker container will use the `.env` that is stored on the host machine.

### After Running The Command
After the command has run, you will need to build your first image on the machine. All you have to do is run the following command

```
docker build -t {my-application} .
```

This will create an image. After this you will be able to run the application.

### Running the applications
Simply type the following

```
docker compose up
```
or
```
docker compose up -d
```

### MySQL Configuration
If you have chosen to use the MySQL variant, you will need to update the `DOCKER_DB_PASSWORD` variable. While by default the docker container isn't exposed
to a host port, it's good security practice to set a good password within the container.

When you run `docker compose up` you may get an error saying the database couldn't connect. This is because you need to update your `.env` to point to the docker
container's host and password. To do this simply change the `DB_HOST` to `my-application-mysql` (replacing my-application with the name you entered in the command) - also
make sure the `DB_USERNAME` is set to "root" and the `DB_PASSWORD` matches your `DOCKER_DB_PASSWORD` variable.

### SQLite Configuration
By default, a fresh SQLite database will be created and stored in the `database` volume for the project. This is a named volume which will persist. 

### Scheduler & Queue
This package provides configuration for a Laravel Scheduler and Queue out of the box. Simply comment out the lines in the docker-compose file if you would like to use a scheduler and queue for the application.

### Deployments
A convenient `deploy.sh` has been provided in this framework. You can run this on your host to automatically pull from your main branch and rebuild the Docker container.

### Proxies
It's recommended to reverse proxy traffic into the port of the web container rather than running the container directly on port 80 or 443. You can use the `TRUSTED_PROXIES` .env variable to restrict traffic to specific CIDR ranges or IPs.

## Also... bring your own configuration!

This is just a starting point to get your application live as quickly as possible. Once this package has created the baseline files, configure away.
