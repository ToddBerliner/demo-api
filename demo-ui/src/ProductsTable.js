import React from 'react';
import Table from 'react-bootstrap/Table';
import Card from 'react-bootstrap/Card';
import Button from 'react-bootstrap/Button';

class ProductsTable extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            products: this.props.products
        }
    }

    componentWillReceiveProps(nextProps, nextContext) {
        // NOTE: this is not the correct way, but stuck on why passing new props
        // to this child from App.js wasn't causing re-render and needed to move
        // on.
        this.setState({products: nextProps.products});
    }

    render() {
        return (
            <Card>
                <Card.Body>
                    <Table striped bordered hover>
                        <thead>
                        <tr>
                            <th> </th>
                            <th>ID</th>
                            <th>SKU</th>
                            <th>Alt. SKU</th>
                            <th>Merchant ID</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Weight</th>
                            <th>Length</th>
                            <th>Height</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        {this.state.products.map(product => (
                            <tr key={product.id}>
                                <td>
                                    <Button variant="danger" size="sm" onClick={() => this.props.deleteHandler(product.id)}>
                                        Delete
                                    </Button>
                                </td>
                                <td>{product.id}</td>
                                <td>{product.sku}</td>
                                <td>{product.alt_sku}</td>
                                <td>{product.merchant_id}</td>
                                <td>{product.description}</td>
                                <td>{product.unit_price}</td>
                                <td>{product.weight}</td>
                                <td>{product.length}</td>
                                <td>{product.height}</td>
                                <td>{product.quantity}</td>
                            </tr>
                        ))}
                        </tbody>
                    </Table>
                </Card.Body>
            </Card>
        );
    }

}

export default ProductsTable;