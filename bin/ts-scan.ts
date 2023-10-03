import * as ts from "typescript";
import * as fs from "fs-extra";
import * as path from "path";

const directoryPath = process.argv[2]; // Update this with the path to your TypeScript files

// Iterate over TypeScript files in a directory
async function getTypescriptFiles(dir: string): Promise<string[]> {
    const files = await fs.readdir(dir);
    const tsFiles: string[] = [];
    for (const file of files) {
        const filePath = path.join(dir, file);
        const stat = await fs.stat(filePath);
        if (stat.isDirectory()) {
            tsFiles.push(...await getTypescriptFiles(filePath));
        } else if (filePath.endsWith('.ts') || filePath.endsWith('.tsx')) {
            tsFiles.push(filePath);
        }
    }
    return tsFiles;
}
// Updated function to extract method parameters and their types
function extractClassesAndMethods(filePath: string) {
    const fileContent = fs.readFileSync(filePath).toString();
    const sourceFile = ts.createSourceFile(filePath, fileContent, ts.ScriptTarget.Latest, true);

    const classes: { [className: string]: { [methodName: string]: { name: string, type: string | null }[] } } = {};

    const visitNode = (node: ts.Node) => {
        if ((ts.isClassDeclaration(node) || ts.isInterfaceDeclaration(node)) && node.name) {
            const className = node.name.text;
            classes[className] = {};
            for (const member of node.members) {
                if (ts.isMethodDeclaration(member) && member.name) {
                    const methodName = (member.name as ts.Identifier).text;
                    classes[className][methodName] = [];
                    // todo skip methods for now
                    // member.parameters.map(param => {
                    //     const parameterName = (param.name as ts.Identifier).text;
                    //     let parameterType = null;
                    //     if (param.type) {
                    //         // For simplicity, we're extracting the textual representation of the type.
                    //         // For more complex types, additional parsing might be needed.
                    //         parameterType = param.type.getText(sourceFile);
                    //     }
                    //     return { name: parameterName, type: parameterType };
                    // });
                }
            }
        }
        ts.forEachChild(node, visitNode);
    };

    visitNode(sourceFile);
    return classes;
}

type GenerateQuizzesParams = {
    [filePath: string]: { [className: string]: { [methodName: string]: { name: string, type: string | null }[] } }
};

// Update the quiz generation function to display method arguments and their types
function generateQuizzes(data: GenerateQuizzesParams) {
    for (const [filePath, classes] of Object.entries(data)) {
        console.log(`File: ${filePath}`);
        for (const [className, methods] of Object.entries(classes)) {
            console.log(`  #class ${className}`);
            for (const [methodName, parameters] of Object.entries(methods)) {
                const parameterStrings = parameters.map(param => `${param.name}: ${param.type}`);
                console.log(`    ->${methodName}(${parameterStrings.join(', ')})`);
            }
        }
    }
}


// Main function
async function main() {
    const tsFiles = await getTypescriptFiles(directoryPath);
    const data: GenerateQuizzesParams = {};

    for (const filePath of tsFiles) {
        data[filePath] = extractClassesAndMethods(filePath);
    }

    return JSON.stringify(data);
}

main().then(console.log);
