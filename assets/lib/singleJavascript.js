export default class {
    constructor(id, alias){
        this.id = id;
        this.alias = alias
    }

    describe(){
        return `${this.id} has alias ${this.alias}.`
    }
}