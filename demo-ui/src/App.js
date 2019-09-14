import React from 'react';
import ProductsTable from "./ProductsTable";
import ProductAdder from "./ProductAdder";

const API = "http://localhost:2323/api/products";

class App extends React.Component {

    constructor(props) {
        super(props);
        this.loadProducts = this.loadProducts.bind(this);
        this.state = {
            error: null,
            isLoaded: false,
            products: []
        };
    }

    componentDidMount() {
        this.loadProducts();
    }

    loadProducts() {

        // get all products
        fetch(API)
            .then(response => response.json())
            .then(
                response => {
                    if (response.errors) {
                        this.setState({
                            error: response.errors,
                            isLoaded: true
                        });
                    }
                    if (response.items) {
                        this.setState({
                            products: response.items,
                            isLoaded: true
                        });
                    }
                }
            )
            .catch(err => {
                this.setState({
                    error: err,
                    isLoaded: true
                });
            })
    }

    render() {
        const {error, isLoaded, products } = this.state;
        if (error) {
            return <div>Error!</div>
        } else if (!isLoaded) {
            return <div>Loading...</div>
        } else {
            return (
                <div>
                    <ProductAdder productAddedHandler={this.loadProducts} />
                    <ProductsTable products={products} />
                </div>
            )
        }
    }
}

export default App;
