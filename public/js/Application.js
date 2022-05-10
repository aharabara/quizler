class Application {
    constructor() {
        this.question = ""
    }

    onNewQuestion() {
        console.log("New question?")
    }

    onNext() {
        fetch('/question?id=10')
            .then(r => r.text())
            .then(data => console.log(data))
        console.log('Next question.')
    }

    onPrevious() {
        fetch('/question?id=10').then((r) => {
            console.log(r)
        })
        console.log('Previous question.')
    }

    onSubmit() {
        console.log('Response is:', document.getElementById('response').value)
    }
}