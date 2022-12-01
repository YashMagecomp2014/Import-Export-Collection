import React, { useEffect, useState } from "react"
import { appconfig } from "../config/config"
import CollectionPage from "./CollectionPage"
import { GlobalAPIcall } from "../config/ApiUtils";
import '../../../css/app.css';
import { Toast, Frame, Page, Button } from '@shopify/polaris';
import { useNavigate } from "@shopify/app-bridge-react";
import { Redirect } from '@shopify/app-bridge/actions';
import { Loading, useAppBridge } from '@shopify/app-bridge-react';
import { useDispatch } from "react-redux";
import { enableLoadHistory, setRedirectIndex } from "../redux/rootReducer";


function Dropdown({ setselectvalue }) {
  const dispatch = useDispatch();
  const app = useAppBridge();
  const [collections, setUsers] = useState([]);
  const [selectvalue, setSelect] = useState();
  const [active, setActive] = useState(false);
  const [Plan, setPlan] = useState([]);
  const [showPlan, setShowPlan] = useState(false)
  const [ActivePlan, setActivePlan] = useState(false)


  const toggleActive = () => {
    setActive(false);
  }

  const chargedata = async () => {
    var res = await GlobalAPIcall('GET', '/getchargeid');

    if (res.charge_id) {
      setShowPlan(false);
      setActivePlan(true)
    } else {
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


    if (selectvalue == 'export_collection') {
      setActive(true);
      var res = await GlobalAPIcall('GET', '/file-export');
      setUsers(res);
      dispatch(enableLoadHistory());
      dispatch(setRedirectIndex(true));
      setActive(false);

    } else if (selectvalue == 'export_collection_with_product') {
      setActive(true);
      var res = await GlobalAPIcall('GET', '/fileExportwithproduct');
      setUsers(res);
      dispatch(enableLoadHistory());
      dispatch(setRedirectIndex(true));
      setActive(false);

    }
    else if (selectvalue == 'export_All_Product') {
      setActive(true);
      var res = await GlobalAPIcall('GET', '/GetAllProduct');
      setUsers(res);
      setActive(false);
      setselectvalue();

    }
    else if (selectvalue == 'export_All_Product_Not_Any_Collection') {
      setActive(true);
      var res = await GlobalAPIcall('GET', '/GetAllProductNotInAnyCollection');
      setUsers(res);
      setActive(false);
      setselectvalue();

    }

  }

  useEffect(() => {
    chargedata()
  }, [])

  return (
    <>
      <div className="row">
        <div className="col-md-12">
          <div className="row" id="maindropdown">
            <div className="col-md-2"></div>
            <div className="col-lg-8">
              <select className="form-select" id="maindropdownselect" aria-label="Default select example" name="selectvalue" defaultValue={'DEFAULT'} onChange={(e) => setSelect(e.target.value)}>
                <option value="DEFAULT" disabled>Please Select</option>
                <option value="export_collection">Get All Collection</option>
                <option value="export_collection_with_product">Get All Collection With Product</option>
                <option value="export_All_Product">Get All Product</option>
                <option value="export_All_Product_Not_Any_Collection">Get All Product Not In Any Collection</option>
              </select>
            </div>
            <div className="col-md-2"></div>
          </div>
          <div className="row">
            <div className="col-lg-4"></div>
            <div className="col-lg-4">
              <button className="btn btn-success" id="maindropdownbtn" onClick={fetchData}>Export</button>
            </div>
            <div className="col-lg-4"></div>
          </div>
        </div>



        {/* {showPlan &&
          <>
            <div className="col-md-1"></div>
            <div className="col-md-3" id="plan">
              <h1 className="headingtrial">You are in trial</h1>
              <ul>
                <li><p className="trialtext">You are in trial mode and limited to importing/exporting 10 collections to test the app. Please Upgrade to Pro to unlock unlimited collections.{Plan.confirmationUrl}</p></li>
              </ul>
              <button className="planbutton" onClick={planapi}>Upgrade To Pro At $19.99 / MO</button>
            </div>
          </>}
        {ActivePlan &&
          <>
            <div className="col-md-1"></div>
            <div className="col-md-3" id="Activeplan">
              <h1 className="headingActive">You are Activate plan</h1>
              <p className="Activetext">$19.99/MO</p>
            </div>
          </>} */}
      </div>



      <Frame style={{ display: 'none', height: '10px' }}> {active && <Toast content="Import File Started" onDismiss={toggleActive} />}</Frame>
    </>

  );
}

export default Dropdown;