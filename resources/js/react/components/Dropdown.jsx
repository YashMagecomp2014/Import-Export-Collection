import React, { useEffect, useState } from "react"
import { appconfig } from "../config/config"
import CollectionPage from "./CollectionPage"
import { GlobalAPIcall } from "../config/ApiUtils";
import '../../../css/app.css';
import { Toast, Frame, Page, Button } from '@shopify/polaris';


function Dropdown() {
  const [collections, setUsers] = useState([]);
  const [selectvalue, setSelect] = useState();
  const [active, setActive] = useState(false);


  const toggleActive = () => {
    setActive(false);
  }

  const fetchData = async () => {
    setActive(true);

    if (selectvalue == 'export_collection') {

      var res = await GlobalAPIcall('GET', '/file-export');
      setUsers(res);

      setActive(false);


    } else if (selectvalue == 'export_collection_with_product') {

      var res = await GlobalAPIcall('GET', '/fileExportwithproduct');
      setUsers(res);
      setActive(false);


    }
    else if (selectvalue == 'export_All_Product') {

      var res = await GlobalAPIcall('GET', '/GetAllProduct');
      setUsers(res);
      setActive(false);


    }
    else if (selectvalue == 'export_All_Product_Not_Any_Collection') {

      var res = await GlobalAPIcall('GET', '/GetAllProductNotInAnyCollection');
      setUsers(res);
      setActive(false);


    }

  }

  const popupBox = () => {
    setTimeout(() => alert("Export Collection Started"), 1000)
  }

  return (
    <div className="container" id="container1">
      <div className="row" id="maindropdown">
        <div className="col-lg-3"></div>
        <div className="col-lg-4">
          <select className="form-select" id="maindropdownselect" aria-label="Default select example" name="selectvalue" defaultValue={'DEFAULT'} onChange={(e) => setSelect(e.target.value)}>
            <option value="DEFAULT">--Please Select--</option>
            <option value="export_collection">Get All Collection</option>
            <option value="export_collection_with_product">Get All Collection With Product</option>
            <option value="export_All_Product">Get All Product</option>
            <option value="export_All_Product_Not_Any_Collection">Get All Product Not In Any Collection</option>
          </select>
        </div>
        <div className="col-lg-2">
          <button className="btn btn-success" id="maindropdownbtn" onClick={fetchData}>Export</button>
        </div>
        <div className="col-lg-3"></div>

      </div>
      {active && <Frame><Toast content="Import File Started" onDismiss={toggleActive} /></Frame>}
    </div>

  );
}

export default Dropdown;