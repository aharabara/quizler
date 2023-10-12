### How to install?
```sh
#clone 
git clone https://github.com/aharabara/quizler
cd quizler

# deply database
docker-compose up
php ./bin/console d:m:m

# start basic http server
symfony server:start
```

### How to generate a quiz?

```
# PHP package quiz
php ./bin/console quiz:generate ./vendor/<vendor>/<package>

# for Symfony bundles (requires bundle to be part of quizler project, WIP)
php ./bin/console quiz:generate --config bundle_config_name 

# Typescript quiz
php ./bin/console quiz:generate ./node_modules/<path-to-package-root>
```


### How to repeat this project?

#### Required packages:
- `symfony/finder`
- `symfony/console`
- `symfony/serializer`
- `symfony/yaml`

#### Steps
1. Create a quiz file (*.yaml)
2. Replicate it into classes like `Quiz` and `Question`
3. Create a `QuizLoader` that will load a file into a `Quiz` structure using a serializer.
4. Setup a console command to iterate over a `Quiz`. 
