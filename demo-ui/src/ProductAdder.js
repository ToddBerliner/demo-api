import React from 'react';
import Card from 'react-bootstrap/Card';
import Collapse from "react-bootstrap/Collapse";
import Button from 'react-bootstrap/Button';
import Form from 'react-bootstrap/Form';
import Col from 'react-bootstrap/Col';
import Feedback from "react-bootstrap/Feedback";

const API = "https://toddberliner.us/shipwire/demo-api/public/api.php/products";
// const API = "http://localhost:2323/api/products";

class ProductAdder extends React.Component {

    constructor(props) {
        super(props);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.state = {
            isEditing: false,
            errors: {}
        };
    }

    handleSubmit(event) {
        event.preventDefault();
        event.stopPropagation();

        const form = event.currentTarget;
        const formData = new FormData(form);

        const productData = {merchant_id: 1}; // this should be selectable by user
        ['sku','alt_sku','description','unit_price','weight','length','height','quantity'].map(key => {
            productData[key] = (formData.get(key) === "")
                ? null
                : formData.get(key);
        });
        // POST to the API
        fetch(API, {
            method: 'POST',
            body: JSON.stringify({product: productData})
        })
            .then(response => {
                // check for 201 or 204, neither of which return content
                if (response.status === 201 || response.status === 204) {
                    // call handler that should reload the data
                    this.setState({errors: {}});
                    form.reset();
                    this.props.productAddedHandler();
                } else {
                    return response.json();
                }
            })
            .catch(error => console.error(error))
            .then(response => {
                if (typeof(response) !== 'undefined') {
                    if (response.errors) {
                        const errors = response.errors;
                        this.setState({errors});
                    }
                }
            });
    }

    render() {
        const { isEditing, errors } = this.state;
        return(
            <Card className="mb-2">
                <Card.Body>
                    <Card.Title>
                        Add Product
                    </Card.Title>
                    <Form onSubmit={this.handleSubmit}>
                        <Form.Row>
                            <Form.Group as={Col} controlId="description">
                                <Form.Label>Description</Form.Label>
                                <Form.Control
                                    name="description"
                                    placeholder="250 characters max"
                                    isInvalid={errors && errors.description}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.description}
                                </Form.Control.Feedback>
                            </Form.Group>
                        </Form.Row>
                        <Form.Row>
                            <Form.Group as={Col} controlId="sku">
                                <Form.Label>SKU</Form.Label>
                                <Form.Control
                                    name="sku"
                                    placeholder="Alpha numeric, 16 characters max"
                                    isInvalid={errors && errors.sku}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.sku}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="alt_sku">
                                <Form.Label>Alt. SKU</Form.Label>
                                <Form.Control
                                    name="alt_sku"
                                    placeholder="Alpha numeric, 16 characters max"
                                    isInvalid={errors && errors.alt_sku}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.alt_sku}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="unit_price">
                                <Form.Label>Unit Price</Form.Label>
                                <Form.Control
                                    name="unit_price"
                                    placeholder="$0.00"
                                    isInvalid={errors && errors.unit_price}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.unit_price}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="weight">
                                <Form.Label>Weight</Form.Label>
                                <Form.Control
                                    name="weight"
                                    placeholder="0.0000"
                                    isInvalid={errors && errors.weight}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.weight}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="length">
                                <Form.Label>Length</Form.Label>
                                <Form.Control
                                    name="length"
                                    placeholder="0.0000" isInvalid={errors && errors.length}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.length}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="height">
                                <Form.Label>Height</Form.Label>
                                <Form.Control
                                    name="height"
                                    placeholder="0.0000" isInvalid={errors && errors.height}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.height}
                                </Form.Control.Feedback>
                            </Form.Group>
                            <Form.Group as={Col} controlId="quantity">
                                <Form.Label>Quantity</Form.Label>
                                <Form.Control
                                    name="quantity"
                                    placeholder="0.0000" isInvalid={errors && errors.quantity}
                                />
                                <Form.Control.Feedback type="invalid">
                                    {errors.quantity}
                                </Form.Control.Feedback>
                            </Form.Group>
                        </Form.Row>
                        <Button type="submit" variant="primary">Add</Button>
                    </Form>
                </Card.Body>
            </Card>
        );
    }
}

export default ProductAdder;