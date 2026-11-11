import SellerProductForm from '../../../Components/SellerProductForm';

export default function Edit(props) {
    const { product } = props;

    return <SellerProductForm {...props} action={`/satici/urunler/${product.id}`} method="put" />;
}
