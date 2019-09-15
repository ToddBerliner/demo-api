import React from 'react';
import ProductsTable from "./ProductsTable";
import ProductAdder from "./ProductAdder";

// const API = "https://toddberliner.us/shipwire/demo-api/public/api.php/products";
const API = "http://localhost:2323/api/products";

class App extends React.Component {

    constructor(props) {
        super(props);
        this.loadProducts = this.loadProducts.bind(this);
        this.deleteProduct = this.deleteProduct.bind(this);
        this.state = {
            error: null,
            isLoaded: false,
            products: []
        };
    }

    componentDidMount() {
        this.loadProducts();
    }

    deleteProduct(product_id) {
        fetch(`${API}/${product_id}`, {
            method: 'delete',
            mode: 'cors',
        })
            .then(response => {
                // no content to deal with, just status
                if (response.status === 204) {
                    // successfully deleted, reload table
                    this.loadProducts();
                } else if (response.status === 422) {
                    // TODO: general UI feedback design - error messages that are not
                    // form field specific + success feedback such as "Product Created" toast
                    alert('There was a problem deleting this product, please try again.');
                }
            })
            .catch(err => {console.log(err)})
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
                    <ProductsTable products={products} deleteHandler={this.deleteProduct} />
                </div>
            )
        }
    }
}

export default App;
