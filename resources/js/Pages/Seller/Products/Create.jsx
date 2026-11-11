import SellerProductForm from '../../../Components/SellerProductForm';

export default function Create(props) {
    return (
        <SellerProductForm
            {...props}
            action="/satici/urunler"
            method="post"
        />
    );
}
