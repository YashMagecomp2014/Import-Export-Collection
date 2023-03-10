import React, { useEffect, useState } from "react";
import { GlobalAPIcall } from "../config/ApiUtils";
import '../../../css/app.css';
import { Redirect } from '@shopify/app-bridge/actions';
import { useAppBridge } from "@shopify/app-bridge-react";
import { setRedirectIndex } from "../redux/rootReducer";
import { useDispatch } from "react-redux";

export const PlanComponent = () => {
    const dispatch = useDispatch()
    const app = useAppBridge();
    const [ActivePlan, setActivePlan] = useState();
    const [YearlyPlan, setYearlyPlan] = useState(true);

    const planapi = async (e) => {

        if(e == 1){
            window.location.reload()
            dispatch(setRedirectIndex(true));
        }
        var plan = new FormData();
        plan.append("plan", e)
        var res = await GlobalAPIcall('POST', '/SubscriptionPlan', plan);
        const data = await res.confirmationUrl;
        const redirect = Redirect.create(app);
        redirect.dispatch(Redirect.Action.REMOTE, data);

    }
    const chargedata = async () => {
        var res = await GlobalAPIcall('GET', '/getchargeid');
        console.log("res.plan");
        console.log(res.plan);
        if (res.plan == 1) {
            setActivePlan(1);
        } else if (res.plan == 2) {
            setActivePlan(2)
        } else if (res.plan == 3) {
            setActivePlan(3)
        }
    }

    useEffect(() => {
        chargedata();
        console.log(ActivePlan);
    }, [])
    return (
        <>
            <div className="row">
                <div className="col-md-4"></div>
                <div className="col-md-4">
                    <h1 className="chooseplan">CHOOSE PLAN</h1>
                </div>
                <div className="col-md-4"></div>
            </div>
            <div className="row">
                <div className="col-md-4">
                    <div className="card text-center">
                        <div className="card-header">
                            Monthly
                        </div>
                        <div className="card-body">
                            <h5 className="card-title">Free</h5>
                            <p className="card-text">Up to 5 Collections import / export for free</p>
                            <div className="card-btn">
                                {/* {ActivePlan == 1 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 1 ? <p className="upgrade">Down Grade</p> : <button value={1} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>} */}
                                {ActivePlan == 1 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 1 ? <button value={1} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button> : <button value={1} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card text-center">
                        <div className="card-header">
                            Monthly
                        </div>
                        <div className="card-body">
                            <h5 className="card-title">$2.99/<sub>Month</sub></h5>
                            <p className="card-text">Up to 50 Collection Import / Export</p>
                            <div className="card-btn">
                                {/* {ActivePlan == 2 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 2 ? <p className="upgrade">Down Grade</p> : <button value={2} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>} */}
                                {ActivePlan == 2 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 2 ? <button value={2} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button> : <button value={2} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card text-center">
                        <div className="card-header">
                            Yearly @20% Discount
                        </div>
                        <div className="card-body">
                            <h5 className="card-title">$4.99/<sub>Month</sub></h5>
                            <p className="card-text">Import / Export Unlimited collections</p>
                            <div className="card-btn">
                                {/* {ActivePlan == 3 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 3 ? <p className="upgrade">Down Grade</p> : <button value={3} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>} */}
                                {ActivePlan == 3 ? <button href="#" className="disabled" disabled>Current Active Plan</button> : ActivePlan > 3 ? <button value={3} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button> : <button value={3} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button>}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </>
    );
}

export default PlanComponent;