import React, { useEffect, useState } from "react"
import { appconfig } from "../config/config"
import CollectionPage from "./CollectionPage"
import { GlobalAPIcall } from "../config/ApiUtils";
import '../../../css/app.css';
import { Toast, Frame, Page, Button } from '@shopify/polaris';
import { useNavigate } from "@shopify/app-bridge-react";
import { Redirect } from '@shopify/app-bridge/actions';
import { Loading, useAppBridge } from '@shopify/app-bridge-react';


function Dropdown({setselectvalue}) {
  const app = useAppBridge();
  const [collections, setUsers] = useState([]);
  const [selectvalue, setSelect] = useState();
  const [active, setActive] = useState(false);
  const [Plan, setPlan] = useState([]);
  const [showPlan, setShowPlan] = useState(false)


  const toggleActive = () => {
    setActive(false);
  }

  const chargedata = async () => {
    var res = await GlobalAPIcall('GET', '/getchargeid');

    console.log(res.charge_id);
    if(res.charge_id){
      setShowPlan(false);
    }else{
      setShowPlan(true);
    }
  }

  const planapi = async () => {

    var res = await GlobalAPIcall('GET', '/SubscriptionPlan');
    const data = await res.confirmationUrl;
    const redirect = Redirect.create(app);
    redirect.dispatch(Redirect.Action.REMOTE, data);

  }

  const fetchData = async () => {
    setActive(true);

    if (selectvalue == 'export_collection') {

      var res = await GlobalAPIcall('GET', '/file-export');
      setUsers(res);
      setselectvalue();
      setActive(false);

    } else if (selectvalue == 'export_collection_with_product') {

      var res = await GlobalAPIcall('GET', '/fileExportwithproduct');
      setUsers(res);
      setActive(false);
      setselectvalue();

    }
    else if (selectvalue == 'export_All_Product') {

      var res = await GlobalAPIcall('GET', '/GetAllProduct');
      setUsers(res);
      setActive(false);
      setselectvalue();

    }
    else if (selectvalue == 'export_All_Product_Not_Any_Collection') {

      var res = await GlobalAPIcall('GET', '/GetAllProductNotInAnyCollection');
      setUsers(res);
      setActive(false);
      setselectvalue();

    }

  }

  const popupBox = () => {
    setTimeout(() => alert("Export Collection Started"), 1000)
  }

  useEffect(() => {
    chargedata()
  }, [])

  return (
    <div className="container" id="container1">
      <div className="row">
        <div className="col-md-8">
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

        </div>
       
        { showPlan && 
         <>
         <div className="col-md-1"></div>
        <div className="col-md-3" id="plan">
          <h1 className="headingtrial">You are in trial</h1>
          <ul>
          <li><p className="trialtext">You are in trial mode and limited to importing/exporting 10 collections to test the app. Please Upgrade to Pro to unlock unlimited collections.{Plan.confirmationUrl}</p></li>
          </ul>
          <button className="planbutton" onClick={planapi}>Upgrade To Pro At $19.99 / MO</button>
        </div>
        </> }
      </div>



      <Frame style={{ display: 'none', height: '10px' }}> {active && <Toast content="Import File Started" onDismiss={toggleActive} />}</Frame>
    </div>

  );
}

export default Dropdown;