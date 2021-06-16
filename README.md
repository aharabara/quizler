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